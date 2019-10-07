<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\AffiliatePostback;
use App\Models\AffiliateSetting;
use App\Models\OdinOrder;


/**
 * Affiliate Service class
 */
class AffiliateService
{
    /**
     * Check if need to send affiliate postback
     * @param AffiliateSetting $affiliate
     * @param array $params
     */
    public function checkAffiliatePostback(string $affiliateId, OdinOrder $order)
    {
        $postbacks = AffiliatePostback::where(['ho_affiliate_id' => $affiliateId])->get();
        
        if ($postbacks) {
            foreach ($postbacks as $postback) {
                $url = $postback->url;
                // check and replace params
                $params = $order->params;
                if ($params){
                    foreach ($params as $key => $value) {
                        $param = '#'.strtoupper($key).'#';
                        $url = str_replace($param, $value, $url);
                    }
                }
                
                // replace currency
                $url = str_replace('#CUR#', $order->currency, $url);
                
                // replace main sku
                $sku = $order->getMainSku();
                if ($sku && !isset($params['product'])) {
                    $url = str_replace('#PRODUCT#', $sku, $url);
                }
                
                // replace amount
                $url = str_replace('#AMOUNT#', $order->total_price, $url);
                
            }
        }
    }
}
