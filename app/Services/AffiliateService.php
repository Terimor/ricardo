<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\AffiliatePostback;
use App\Models\AffiliateSetting;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Models\RequestQueue;
use App\Models\Pixel;
use App\Services\ProductService;
use Illuminate\Http\Request;


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
                
                // replace PRODUCT_NAME
                $url = static::replaceProductNameBySku($sku, $url);
                                
                // replace amount
                $url = str_replace('#AMOUNT#', $order->total_price, $url);
                
                // replace tpl if needed
                if (!isset($params['tpl'])) {
                    $url = str_replace('#TPL#', AffiliateService::DEFAULT_TPL, $url);
                }
                
                // replace country
                $url = str_replace('#COUNTRY#', strtoupper($order->shipping_country), $url);
                
                // replace COP_ID
                $copId = $order->getPriceSet();
                if ($copId) {
                    $url = str_replace('#COP_ID#', $copId, $url);
                }
                
                // send request query
                RequestQueue::saveNewRequestQuery($url, $postback->delay);
            }
        }        
    }
    
    /**
     * Return string with replaced params
     * @param string $string
     * @param array $params
     */
    public static function replaceQueryParams(string $string, array $params): string
    {
        if ($params){
            foreach ($params as $key => $value) {
                $param = '#'.strtoupper($key).'#';
                $string = str_replace($param, $value, $string);
            }
        }
        return $string;
    }
    
    /**
     * Return replaced text
     * @param string $sku
     * @param string $string
     * @return string
     */
    public static function replaceProductNameBySku(string $sku, string $string): string
    {        
        $skus = OdinProduct::getCacheSkusProduct();
        if ($sku) {
            $productName = !empty($skus[$sku]['product_name']) ? $skus[$sku]['product_name'] : '';
            if ($productName) {
                $string = str_replace('#PRODUCT_NAME#', $skus[$sku]['product_name'], $string);
            }
        }
        return $string;
    }
    
    /**
     * Get all pixels
     */
    public static function getPixels(Request $request, AffiliateSetting $affiliate = null)
    {
        $pixels = [];
        if ($affiliate) {
            $productService = new ProductService();
            $product = $productService->resolveProduct($request, false, null, true);
            $countryCode = \Utils::getLocationCountryCode();
            
            $route = $request->route()->getName() ? $request->route()->getName() : 'index';
            $device = \Utils::getDevice();
            
            // get pixels
            $pixels = Pixel::getPixelsByData($request, $product, $countryCode, $route, $device);
            
        }
        return $pixels;
    }        
}
