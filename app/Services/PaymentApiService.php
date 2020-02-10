<?php

namespace App\Services;
use App\Models\PaymentApi;
use App\Models\PaymentLimit;
use Illuminate\Database\Eloquent\Collection;

/**
 * PaymentApi Service class
 */
class PaymentApiService
{

    const PAYMENT_HIGH_LIMIT_PCT = 95;

    /**
     * Returns PaymentApi by domain
     * @param  string $domain_id
     * @param  Collection  $apis
     * @param  string|null $currency
     * @return PaymentApi|null
     */
    private static function getByDomainId(string $domain_id, Collection $apis, ?string $currency = null): ?PaymentApi
    {
        $keys = $apis->filter(function($v) use ($domain_id) { return in_array($domain_id, $v->domain_ids); })->all();

        if ($currency) {
            return PaymentLimit::getAvailable($keys, $currency, self::PAYMENT_HIGH_LIMIT_PCT);
        }
        return array_shift($keys);
    }

    /**
     * Returns PaymentApi by product
     * @param  string $product_id
     * @param  Collection  $apis
     * @param  string|null $currency
     * @return PaymentApi|null
     */
    private static function getByProductId(string $product_id, Collection $apis, ?string $currency = null): ?PaymentApi
    {
        $keys = $apis->filter(function($v) use ($product_id) { return in_array($product_id, $v->product_ids); })->all();
        if ($currency) {
            return PaymentLimit::getAvailable($keys, $currency, self::PAYMENT_HIGH_LIMIT_PCT);
        }
        return array_shift($keys);
    }

    /**
     * Returns default PaymentApi
     * @param  Collection  $apis
     * @param  string|null $currency
     * @return PaymentApi|null
     */
    private static function getDefault(Collection $apis, ?string $currency = null): ?PaymentApi
    {
        $keys = $apis->filter(function($v) { return empty($v->product_ids) && empty($v->domain_ids); })->all();
        if ($currency) {
            return PaymentLimit::getAvailable($keys, $currency, self::PAYMENT_HIGH_LIMIT_PCT);
        }
        return array_shift($keys);
    }

    /**
     * Returns PaymentApi by domain or product
     * @param  string $product_id
     * @param  string|null $domain_id
     * @param  array  $prv_list default=[]
     * @param  string|null $currency default=null
     * @return PaymentApi|null
     */
    public static function getAvailableOne(string $product_id, ?string $domain_id, array $prv_list = [], ?string $currency = null): ?PaymentApi
    {
        if (empty($prv_list)) {
            return null;
        }

        $apis = PaymentApi::getAllByProviders($prv_list);

        $api = null;

        if ($domain_id) {
            $api = self::getByDomainId($domain_id, $apis, $currency);
        }

        if (!$api) {
            $api = self::getByProductId($product_id, $apis, $currency);
        }

        return $api ?? self::getDefault($apis, $currency);
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
