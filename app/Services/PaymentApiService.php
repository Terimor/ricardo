<?php

namespace App\Services;
use App\Models\PaymentApi;
use App\Models\OdinProduct;
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
     * @param  Collection  $apis
     * @param  string|null $domain_id
     * @return PaymentApi|null
     */
    private static function getByDomainId(Collection $apis, ?string $domain_id): ?PaymentApi
    {
        $result = null;
        if ($domain_id) {
            // get PaymentApi by filtering items by domain_id
            // result will be the first element whose domain_ids field contains the search value
            $result = $apis->filter(function($v) use ($domain_id) { return in_array($domain_id, $v->domain_ids); })->first();
        }
        return $result;
    }

    /**
     * Returns PaymentApi by product
     * @param  Collection  $apis
     * @param  string $product_id
     * @return PaymentApi|null
     */
    private static function getByProductId(Collection $apis, string $product_id): ?PaymentApi
    {
        // get PaymentApi by filtering items by product_id
        // result will be the first element whose product_ids (product_category_ids) field contains the search value
        return $apis->filter(function($v) use ($product_id) {
            $product_ids = $v->product_ids;
            if (!empty($v->product_category_ids)) {
                $product_ids = OdinProduct::getProductIdsByCategoryIds($v->product_category_ids);
            }
            return in_array($product_id, $product_ids);
        })->first();
    }

    /**
     * Returns default PaymentApi
     * @param  Collection  $apis
     * @return PaymentApi|null
     */
    private static function getDefault(Collection $apis): ?PaymentApi
    {
        // get PaymentApi by filtering items by domain_ids and product_ids
        // result will be the first element whose domain_ids and product_ids fields and product_category_ids are empty
        return $apis->filter(function($v) {
            return empty($v->product_ids) && empty($v->domain_ids) && empty($v->product_category_ids);
        })->first();
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

        $apis_by_prv = PaymentApi::getAllByProviders($prv_list)->groupBy('payment_provider');

        // get PaymentApis for each provider by sequential filtering (domain->product->default)
        $filtered = [];
        foreach ($apis_by_prv as $v) {
            $api = self::getByDomainId($v, $domain_id);
            if (!$api) {
                $api = self::getByProductId($v, $product_id);
                if (!$api) {
                    $api = self::getDefault($v);
                }
            }
            $filtered[] = $api;
        }

        // filter PaymentApis by currency limits
        if ($currency) {
            $filtered = PaymentLimit::getAvailableApis($filtered, $currency, self::PAYMENT_HIGH_LIMIT_PCT);
        }

        shuffle($filtered);

        return array_pop($filtered);
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
