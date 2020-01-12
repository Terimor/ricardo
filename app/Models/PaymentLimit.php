<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Http\Discovery\NotFoundException;
use App\Constants\PaymentProviders;

/**
 * This is the model class for collection "payment_limit".
 *
 * @property string $payment_api_id
 * @property string $currency
 * @property float $limit_usd
 * @property float $paid_usd
 * @property boolean $is_splitting
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class PaymentLimit extends Model
{
    protected $collection = 'payment_limit';

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Returns Model by payment_api_id
     * @param string $payment_api_id
     * @param string $currency
     * @return PaymentLimit|null
     */
    public static function getOneByPaymentApiId(string $payment_api_id, string $currency = null): ?PaymentLimit
    {
        $query = self::where(['payment_api_id' => $payment_api_id]);
        if ($currency) {
            $query->where('currency', $currency);
        }
        return $query->first();
    }

    /**
     * Returns available limits
     * @param array  $payment_api_ids
     * @param string $currency
     * @param int    $pct
     * @return array
     */
    public static function getAvailable(array $payment_api_ids, string $currency, int $pct): array
    {
        $collection = self::whereIn('payment_api_id', $payment_api_ids)->where('currency', $currency)->get();
        return $collection->filter(function($model) use ($pct) {
            return $model->paid_usd < $model->limit_usd * $pct / 100;
        })->all();
    }

}
