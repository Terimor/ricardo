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
    public static function getAvailable(array $apis, string $currency, int $pct): array
    {
        $api_ids = collect($apis)->map(function($v) { return (string)$v->getIdAttribute(); })->toArray();
        $limits = self::whereIn('payment_api_id', $api_ids)->where('currency', $currency)->get();
        return collect($apis)->filter(function($v) use ($limits, $pct) {
            $limit = $limits->first(function($vv) use ($v) {
                return $v->getIdAttribute() === $vv->payment_api_id;
            });
            if (!empty($limit)) {
                $is_available = $limit->paid_usd < $limit->limit_usd * $pct / 100;
                if ($is_available && $limit->is_splitting) {
                    $is_available = !mt_rand(0, 1);
                }
                return $is_available;
            }
            return true;
        })->all();
    }

}
