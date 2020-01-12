<?php

namespace App\Services;
use App\Models\PaymentApi;
use App\Models\PaymentLimit;

/**
 * PaymentApi Service class
 */
class PaymentApiService
{

    const PAYMENT_HIGH_LIMIT_PCT = 95;

    /**
     * Returns PaymentApi by product_id
     * @param  string $product_id
     * @param  array  $prv_list
     * @param  string $currency
     * @return PaymentApi|null
     */
    public static function getByProductId(string $product_id, array $prv_list = [], string $currency = null): ?PaymentApi
    {
        if (empty($prv_list)) {
            return null;
        }

        $models = PaymentApi::getAllByProviders($prv_list);

        $keys = collect($models)->filter(function($v) use ($product_id) { return in_array($product_id, $v->product_ids); })->all();

        if (count($keys) < count($prv_list)) {
            $defaults = collect($models)->filter(function($v) use ($keys) {
                return empty($v->product_ids) && !collect($keys)->contains('payment_provider', $v->payment_provider);
            })->all();
            $keys = array_merge($keys, $defaults);
        }

        if ($currency) {
            $api_ids = collect($keys)->map(function($v) { return (string)$v->getIdAttribute(); })->toArray();
            $limits = PaymentLimit::getAvailable($api_ids, $currency, self::PAYMENT_HIGH_LIMIT_PCT);
            if (!empty($limits)) {
                $api_id = $limits[0]->payment_api_id;
                return collect($keys)->first(function($v) use ($api_id) { return $api_id === (string)$v->getIdAttribute();  });
            }
        }

        // if ($pref) {
        //     return collect($keys)->first(function($v) use ($pref) { return $v->payment_provider === $pref;  });
        // }

        return array_pop($keys);
    }

    /**
     * Returns PaymentApi by id
     * @param  string $api_id
     * @return PaymentApi|null
     */
    public static function getById(string $api_id): ?PaymentApi
    {
        return PaymentApi::getById($api_id, false);
    }

    public static function usePaymentLimit(string $api_id, string $currency, float $amount_usd): void
    {
        $api = PaymentLimit::getOneByPaymentApiId($api_id, $currency);
        if ($api) {
            $api->paid_usd += $amount_usd;
            $api->save();
        }
    }
}
