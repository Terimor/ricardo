<?php

namespace App\Models;

use App\Services\UtilsService;

/**
 * Class CustomerBlacklist
 * @package App
 *
 * @property string|null email
 * @property string|null fingerprint
 * @property string|null ip
 * @property string|null phone
 * @property string|null address
 * @property float $orders_paid_usd
 * @property string[] orders
 * @property mixed expire_at
 * @property mixed created_at
 * @property mixed updated_at
 */
class CustomerBlacklist extends \Jenssegers\Mongodb\Eloquent\Model
{
    const ROW_LIFETIME_HRS = 72;
    const ADDRESS_SIMILARITY_PCT = 90;

    protected $collection = 'customer_blacklist';

    protected $dates = ['created_at', 'expire_at', 'updated_at'];

    protected $attributes = ['orders' => [], 'orders_paid_usd' => 0];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['address', 'phone', 'ip', 'email', 'fingerprint'];

    /**
     * Returns one by similar address
     * @param string $address
     * @return CustomerBlacklist|null
     */
    public static function findSimilarOne(string $address): ?CustomerBlacklist
    {
        return self::raw(function($collection) use ($address) {
            return $collection
                ->find(
                    ['$text' => ['$search' => $address]],
                    [
                        'projection' => ['score' => ['$meta' => 'textScore']],
                        'sort' => ['score' => ['$meta' => 'textScore']],
                        'limit' => 1
                    ]
                );
        })
            ->filter(function(CustomerBlacklist $item) use ($address) {
                similar_text($item->address ?? '', $address, $pct);
                return $pct > self::ADDRESS_SIMILARITY_PCT;
            })
            ->first();
    }

    /**
     * Returns one by specified fields
     * @param array $cus_data
     * @return CustomerBlacklist|null
     */
    public static function searchOne(array $cus_data): ?CustomerBlacklist
    {
        $query = self::query();
        $or = [];

        if (!empty($cus_data['fingerprint'])) {
            $or[] = ['ip' => $cus_data['ip'], 'fingerprint' => $cus_data['fingerprint']];
        }

        if (!empty($cus_data['phone'])) {
            $or[] = ['phone' => $cus_data['phone']];
        }

        if (!empty($cus_data['email'])) {
            $or[] = ['email' => $cus_data['email']];
        }

        $model = null;
        if (!empty($or)) {
            $model = $query->where(['$or' => $or])->first();
        }

        if (!$model && !empty($cus_data['address'])) {
            $model = self::findSimilarOne($cus_data['address']);
        }
        return $model;
    }

    /**
     * Appends a new or increments an existing one
     * @param string $order_number
     * @param float $txn_amount_usd
     * @param array $cus_data
     * @return CustomerBlacklist
     */
    public static function addOne(string $order_number, float $txn_amount_usd, array $cus_data): CustomerBlacklist
    {
        $model = self::searchOne($cus_data);

        if (!$model) {
            $model = new CustomerBlacklist($cus_data);
            $model->expire_at = UtilsService::getMongoTimeFromTS(time() + CustomerBlacklist::ROW_LIFETIME_HRS * 3600);
        }

        $model->orders = array_unique([...$model->orders, $order_number]);
        $model->orders_paid_usd += $txn_amount_usd;

        if (!$model->save()) {
            $validator = $model->validate();
            if ($validator->fails()) {
                logger()->error("CustomerBlacklist saving", $validator->errors()->all());
            }
        }

        return $model;
    }

    /**
     * Returns orders count
     * @return int
     */
    public function getOrdersCount(): int
    {
        return count($this->orders);
    }

}
