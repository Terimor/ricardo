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
     * Returns available apis
     * @param array  $apis
     * @param string $currency
     * @param int    $pct
     * @return PaymentApi[]
     */
    public static function getAvailableApis(array $apis, string $currency, int $pct): array
    {
        // get PaymentApi ids
        $api_ids = collect($apis)->map(function($v) { return (string)$v->getIdAttribute(); })->toArray();
        // get PaymentLimit collection by PaymentApi ids and currency
        $limits = self::whereIn('payment_api_ids', $api_ids)->where('currency', $currency)->get();

        $availables = [];
        foreach ($apis as $v) {
            // get PaymentLimit by PaymentApi id
            $limit = $limits->first(function($vv) use ($v) {
                return in_array($v->getIdAttribute(), $vv->payment_api_ids);
            });
            // check PaymentLimit for exceeding the limit if it exists
            if (!empty($limit)) {
                $is_available = $limit->paid_usd < $limit->limit_usd * $pct / 100;
                // toss a coin if is_splitting is set
                if ($is_available && $limit->is_splitting) {
                    $is_available = !mt_rand(0, 1);
                }
                if ($is_available) {
                    $availables[] = $v;
                }
                // go to next iteration to prevent duplicates
                continue;
            }
            $availables[] = $v;
        }

        return $availables;
    }

}
