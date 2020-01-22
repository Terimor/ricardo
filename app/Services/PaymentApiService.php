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
            return PaymentLimit::getAvailable($keys, $currency, self::PAYMENT_HIGH_LIMIT_PCT);
        }

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

}
