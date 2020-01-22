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
 * @property array $payment_api_ids
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
     * Returns available limits
     * @param array  $apis
     * @param string $currency
     * @param int    $pct
     * @return PaymentApi|null
     */
    public static function getAvailable(array $apis, string $currency, int $pct): ?PaymentApi
    {
        $api_ids = collect($apis)->map(function($v) { return (string)$v->getIdAttribute(); })->toArray();
        $limits = self::whereIn('payment_api_ids', $api_ids)->where('currency', $currency)->get();
        $available_apis = collect($apis)->filter(function($v) use ($limits, $pct) {
            $limit = $limits->first(function($vv) use ($v) {
                return in_array($v->getIdAttribute(), $vv->payment_api_ids);
            });
            if (!empty($limit)) {
                $is_available = $limit->paid_usd < $limit->limit_usd * $pct / 100;
                if ($is_available && $limit->is_splitting) {
                    $is_available = !mt_rand(0, 1);
                }
                return $is_available;
            }
            return false;
        })->all();

        if (empty($available_apis)) {
            shuffle($apis);
            return array_pop($apis);
        }

        return array_pop($available_apis);
    }

}
