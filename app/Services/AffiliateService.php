<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\AffiliatePostback;
use App\Models\AffiliateSetting;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Models\RequestQueue;
use App\Models\Pixel;
use App\Models\GoogleTag;
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
                if ($sku) {
                    $url = static::replaceProductNameBySku($sku, $url);
                }
                                
                // replace amount
                if (!empty($order->total_price_usd)) {
                    $url = str_replace('#AMOUNT#', $order->total_price_usd, $url);
                }
                
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
     * Return HTML for dispay on APP page
     * @param Request $request
     * @param AffiliateSetting $affiliate
     * @return type
     */
    public static function getHtmlToApp(Request $request, AffiliateSetting $affiliate = null)
    {
        $htmls = [
            'pixels' => AffiliateService::getPixels($request, $affiliate),
            'google' => GoogleTag::getGoogleTagsForDisplay($request, $affiliate)
        ];
        return $htmls;
    }
    
    /**
     * Get pixels
     * @param Request $request
     * @param AffiliateSetting $affiliate
     * @return type
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
            $pixels = AffiliateService::getPixelsByData($request, $affiliate->ho_affiliate_id, $product, $countryCode, $route, $device);
            
        }
        return $pixels;
    }
    
    /**
     * Return array pixel codes
     * @param string $productId
     * @param string $countryCode
     * @param string $route
     * @param string $device
     */
    public static function getPixelsByData(Request $request, string $hoAffiliateID, $product, string $countryCode, string $route, string $device) : array
    {
        $pixels = Pixel::where(['product_ids' => $product->id, 'ho_affiliate_id' => $hoAffiliateID, 'countries' => $countryCode, 'placements' => $route, 'devices' => $device])->get();
        
        $pixelsArray = [];
        foreach ($pixels as $pixel) {
            // skip if direct only true and &direct is't true or 1            
            if ($pixel->is_direct_only && !$request->direct) {                
                continue;
            }            
            // replace query
            $code = AffiliateService::replaceQueryParams($pixel->code, $request->query());
            // replace country                            
            $code = str_replace('#COUNTRY#', $countryCode, $code);
            
            // replace tpl if needed
            if (!isset($request->tpl)) {
                $code = str_replace('#TPL#', AffiliateService::DEFAULT_TPL, $code);
            }
            
            // replace order currency
            if (!isset($request->cur)) {
                $currency = CurrencyService::getCurrency();
                $code = str_replace('#CUR#', $currency->code, $code);
            }           
            
            // replace sku
            if (!isset($request->product)) {
                $skusProduct = $product->skus;
                if (!empty($skusProduct[0]['code'])) {
                    $code = str_replace('#PRODUCT#', $skusProduct[0]['code'], $code);
                    
                    // replace product name
                    $code = AffiliateService::replaceProductNameBySku($skusProduct[0]['code'], $code);
                }
            }
            
            // replace price set
            if (isset($product->price_set)) {
                $code = str_replace('#COP_ID#', $product->price_set, $code);
            }
            
            // get order if order parameter and replace #AMOUNT#
            if (!empty($request->order)) {
                $order = OdinOrder::where('_id', $request->order)->first();
                if ($order) {
                    $code = str_replace('#AMOUNT#', $order->total_price_usd, $code);
                }
            }
            
            $pixelsArray[] = [
                'type' => $pixel->type,
                'code' => $code
            ];                        
        }
        
        return $pixelsArray;        
    }    
}
