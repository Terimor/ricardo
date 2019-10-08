<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\AffiliatePostback;
use App\Models\AffiliateSetting;
use App\Models\OdinOrder;
use App\Models\RequestQueue;


/**
 * Affiliate Service class
 */
class AffiliateService
{
    const DEFAULT_TPL = 'vmp41';
    
    /**
     * Check if need to send affiliate postback
     * @param AffiliateSetting $affiliate
     * @param array $params
     */
    public static function checkAffiliatePostback(string $affiliateId, OdinOrder $order)
    {        
        $postbacks = AffiliatePostback::where(['ho_affiliate_id' => $affiliateId])->get();

        if ($postbacks) {
            foreach ($postbacks as $postback) {
                $url = $postback->url;
                // check and replace params
                $params = $order->params;
                $url = static::replaceQueryParams($url, $params);
                
                // replace order currency
                if (!isset($params['cur'])) {
                    $url = str_replace('#CUR#', $order->currency, $url);
                }
                
                // replace main sku
                $sku = $order->getMainSku();
                if ($sku && !isset($params['product'])) {
                    $url = str_replace('#PRODUCT#', $sku, $url);
                }
                
                // replace amount
                $url = str_replace('#AMOUNT#', $order->total_price, $url);
                
                // replace tpl if needed
                if (!isset($params['tpl'])) {
                    $url = str_replace('#TPL#', self::DEFAULT_TPL, $url);
                }
                
                // replace country
                $url = str_replace('#COUNTRY#', strtoupper($order->shipping_country), $url);
                
                // send request query
                RequestQueue::saveNewRequestQuery($url, $postback->delay);
            }
        }        
    }
    
    /**
     * Return string with replaced params
     * @param string $url
     * @param array $params
     */
    public static function replaceQueryParams(string $url, array $params): string
    {
        if ($params){
            foreach ($params as $key => $value) {
                $param = '#'.strtoupper($key).'#';
                $url = str_replace($param, $value, $url);
            }
        }
        return $url;
    }
}
