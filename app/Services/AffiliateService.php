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
use Illuminate\Support\Facades\Cookie;


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
    public static function checkAffiliatePostback(string $affiliateId, OdinOrder $order, $validTxid = null)
    {        
        $postbacks = AffiliatePostback::all();

        if ($postbacks) {            
            $postbacksArray = $order->postbacks ?? null;
            $isSavePostbacks = false;
            foreach ($postbacks as $postback) {
                // check internal options
                if ($postback->ho_affiliate_id == AffiliateSetting::ALL_EXCLUDE_INTERNAL_OPTION && (int)$affiliateId <= AffiliateSetting::OWN_AFFILIATE_MAX) {                    
                    continue;
                    // if !=0 && -1 is check affiliate
                } else if ($postback->ho_affiliate_id != AffiliateSetting::ALL_INCLUDE_INTERNAL_OPTION && 
                        $postback->ho_affiliate_id != AffiliateSetting::ALL_EXCLUDE_INTERNAL_OPTION && $postback->ho_affiliate_id != $affiliateId) {
                    continue;
                }
                
                $url = $postback->url;
                // check and replace params
                $params = $order->params;
                // if we have #TXID# in code check it then replace to txid
                if (strpos($url, '#TXID#')) {                    
                    if (!empty($order->txid)) {
                        $url = str_replace('#TXID#', $order->txid, $url);
                    }
                }

                // if we have #OFFER_ID# in code check it then replace to offer
                if (strpos($url, '#OFFER_ID#')) {                    
                    if (!empty($order->offer)) {
                        $url = str_replace('#OFFER_ID#', $order->offer, $url);
                    }
                }

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
                
                // domain
                 if (!isset($params['domain'])) {
                     $domain = \Utils::getDomain();
                     $url = str_replace('#DOMAIN#', $domain, $url);
                 }
                
                // send request query
                RequestQueue::saveNewRequestQuery($url, $postback->delay);
                $postbacksArray[] = $url;
                $isSavePostbacks = true;
            }
            if ($isSavePostbacks) {
                $order->postbacks = $postbacksArray;
                $order->save();
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
            'gtags' => GoogleTag::getGoogleTagsForDisplay($request, $affiliate)
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
        $pixels = Pixel::getPixels($product, $countryCode, $route, $device);
                
        $pixelsArray = []; $isShown = false;
        $order = null;
        if (!empty($request->order)) {
            $order = OdinOrder::where('_id', $request->order)->first();
            $pixelsOrderArray = $order->pixels ?? [];
            $events = $order->events ?? [];            
        }
        
        foreach ($pixels as $pixel) {
            $isSavePixelCode = false;
            // skip if direct only true and &direct is't true or 1            
            if ($pixel->is_direct_only && !$request->direct) {                
                continue;
            }
            
            // check internal options
            if ($pixel->ho_affiliate_id == AffiliateSetting::ALL_EXCLUDE_INTERNAL_OPTION && (int)$hoAffiliateID <= AffiliateSetting::OWN_AFFILIATE_MAX) {                    
                continue;
                // if !=0 && -1 is check affiliate
            } else if ($pixel->ho_affiliate_id != AffiliateSetting::ALL_INCLUDE_INTERNAL_OPTION && 
                    $pixel->ho_affiliate_id != AffiliateSetting::ALL_EXCLUDE_INTERNAL_OPTION && $pixel->ho_affiliate_id != $hoAffiliateID) {
                continue;
            }
                   
            $code = $pixel->code;
                        
            //if order, replace #AMOUNT#, #TXID#, #OFFER_ID# and check is_reduced and txns
            if ($order) {                    
                $code = str_replace('#AMOUNT#', $order->total_price_usd, $code);

                // if we have #TXID# in code check it then replace to txid
                if (strpos($code, '#TXID#')) {                    
                    if (!empty($order->txid)) {
                        $code = str_replace('#TXID#', $order->txid, $code);
                    }
                }

                // if we have #OFFER_ID# in code check it then replace to offer
                if (strpos($code, '#OFFER_ID#')) {                    
                    if (!empty($order->offer)) {
                        $code = str_replace('#OFFER_ID#', $order->offer, $code);
                    }
                }                    

            }

            // check sale logic
            if ($pixel->type == Pixel::TYPE_SALE) {
                if (isset($order->is_reduced) && $order->is_reduced && (!$events || !in_array(OdinOrder::EVENT_AFF_PIXEL_SHOWN, $events))) {
                    // skip if flagged and authorized
                    if (isset($order->is_flagged) && $order->is_flagged === true && !$order->isTxnForFlagged()) {
                        continue;
                    }
                    
                    $isSavePixelCode = true; 
                    $isShown = true;
                } else {
                    continue;
                }
            }
            
            // replace query
            $code = AffiliateService::replaceQueryParams($code, $request->query());
            
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
            
            // domain
            if (empty($request->domain)) {
                $domain = \Utils::getDomain();
                $code = str_replace('#DOMAIN#', $domain, $code);
            }
            
            if ($isSavePixelCode) {
                $pixelsOrderArray[] = $code;
            }
            
            $pixelsArray[] = [
                'type' => $pixel->type ?? null,
                'code' => $code
            ];                        
        }
        
        // if we show one sale pixel save events
        if ($isShown) {
            $events[] = OdinOrder::EVENT_AFF_PIXEL_SHOWN;
            $order->events = $events;
            $order->pixels = $pixelsOrderArray;
            $order->save();
        }        
        return $pixelsArray;        
    }
    
    
    /**
     * return valid Txid
     * @param string $txid
     * @return type
     */
    public static function getValidTxid($txid) 
    {
        $correctTxid = null;
        if ($txid && strlen($txid) >= AffiliateSetting::TXID_LENGTH) {
            $correctTxid = $txid;
        } else {
            // get from cookies
            $cookieTxid = Cookie::get('txid');
            if ($cookieTxid && strlen($cookieTxid) >= AffiliateSetting::TXID_LENGTH) {
                $correctTxid = $cookieTxid;
            }
        }
        return $correctTxid;
    }

    /**
     * returns affiliate id from request
     * @param Request $request
     * @return type
     */
    public static function getAffIdFromRequest(Request $request)
    {        
        return $request->get('aff_id') ? $request->get('aff_id') : ($request->get('affid') && $request->get('affid') > AffiliateSetting::OWN_AFFILIATE_MAX ? $request->get('affid') : null);
    }
    
    /**
     * Get attribute by priority
     * @param type $param1
     * @param type $param2
     * @return type
     */
    public static function getAttributeByPriority($param1, $param2)
    {
        return $param1 ? $param1 : ($param2 && $param2 > AffiliateSetting::OWN_AFFILIATE_MAX ? $param2 : null);
    }
}
