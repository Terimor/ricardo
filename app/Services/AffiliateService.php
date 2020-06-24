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
use App\Models\Click;
use App\Models\Domain;
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
                $params = !empty($order->params) ? $order->params : [];
                // set params aff_sub and aff_sub1
                $params = static::replaceAffSubsParams($params);

                // if we have #TXID# in url check it then replace to txid
                $txid = null;
                if (strpos($url, '#TXID#')) {
                    if (!empty($order->txid)) {
                        $txid = static::getValidTxid($order->txid);
                        if ($txid) {
                            $url = str_replace('#TXID#', $txid, $url);
                        }
                    }
                }
                // if txid not valid delete transaction parameter from URL
                if (!$txid) {
                    $url = str_replace(AffiliateSetting::$transactionReplaceArray, '', $url);
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
                $url = !empty($order->total_price_usd) ? str_replace('#AMOUNT#', $order->total_price_usd, $url) : $url;
                // order number
                $url = $order->number ? str_replace('#ORDER_NUMBER#', $order->number, $url) : $url;

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

                 // replace all #tag# after processing
                $url = str_replace('=#', '={', $url);
                $url = str_replace('#', '}', $url);

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
                // check txid validate to replace query params
                if ($key == 'txid') {
                    $value = static::getValidTxid($value);
                    if (!$value) {
                        continue;
                    }
                }
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
        $pixels = Pixel::getPixels($product, $countryCode, $route, $device, $hoAffiliateID);

        $pixelsArray = []; $isShown = false;
        $order = null;
        if (!empty($request->order)) {
            $order = OdinOrder::getById($request->order, false);
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

            //if order, replace #AMOUNT#, #TXID#, #OFFER_ID# and check is_reduced and txns
            $txid = null;
            if ($order) {
                $code = static::replacePixelsOrderData($order, $code, $txid);
            }

            $query = $request->query();
            // check txid and replace only if valid
            if (strpos($code, '#TXID#')) {
                if (isset($query['txid'])) {
                    $txid = static::getValidTxid($query['txid']);
                    if ($txid) {
                        $code = str_replace('#TXID#', $txid, $code);
                    }
                }
            }

            // if txid not valid delete transaction from URL
            if (!$txid) {
                $code = str_replace(AffiliateSetting::$transactionReplaceArray, '', $code);
            }

            // replace query
            $params = self::replaceAffSubsParams($query);
            $code = AffiliateService::replaceQueryParams($code, $params);

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
     * Replace pixel placeholders depends on order
     * @param $order
     * @param string $code
     * @param $txid
     * @return string
     */
    public static function replacePixelsOrderData($order, string $code, &$txid): string {
        $code = str_replace('#AMOUNT#', $order->total_price_usd, $code);

        // if we have #TXID# in code check it then replace to txid
        if (strpos($code, '#TXID#')) {
            if (!empty($order->txid)) {
                $txid = static::getValidTxid($order->txid);
                if ($txid) {
                    $code = str_replace('#TXID#', $txid, $code);
                }
            }
        }
        // if we have #OFFER_ID# in code check it then replace to offer
        if (strpos($code, '#OFFER_ID#')) {
            if (!empty($order->offer)) {
                $code = str_replace('#OFFER_ID#', $order->offer, $code);
            }
        }
        if (strpos($code, '#ORDER_NUMBER#')) {
            if (!empty($order->number)) {
                $code = str_replace('#ORDER_NUMBER#', $order->number, $code);
            }
        }
        return $code;
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
     * returns affiliate id from request
     * @param Request $request
     * @return type
     */
    public static function isAffiliateRequestEmpty(Request $request)
    {
        return empty($request->get('aff_id')) && empty($request->get('affid'));
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

    /**
     * Prepare data to save to click collection
     * @param Request $request
     */
    public function fingerprintClick(Request $request)
    {
        $url = $request->get('url');
        if (!$url) {
            logger()->warning('FingerprintWrongDataUrl', ['request' => $request->all()]);
            $url = $request->get('page');
        }
        $parsedUrl = parse_url($url);

        $page = null;
        if (isset($parsedUrl['path'])) {
            $page = Click::getPageByPath($parsedUrl['path']);
        }

        if ($page && $request->get('f')) {
            $priceSet = null;
            // check exists by cop_id parameter
            if ($request->get('cop_id')) {
                $exists = OdinProduct::isExistsByCopId($request->get('cop_id'));
                if ($exists) {
                    $priceSet = $request->get('cop_id');
                } else {
                    logger()->warning("CantFindCopIdApply", ['url' => $url]);
                }
            }
            // check by product parameter
            if (!$priceSet && $request->get('product')) {
                $product = OdinProduct::getBySku($request->get('product'), false, ['prices.price_set']);
                if ($product) {
                  $product->skip_prices = true;
                  $prices = $product['prices'];
                  $priceSet = $prices['price_set'] ?? null;
                } else {
                  logger()->warning("CantFindProductApply", ['url' => $url]);
                }
            }

            // check by domain
            if (!$priceSet) {
                $priceSet = Domain::getPriceSet();
            }

            $ip = $request->ip();
            $location = \Location::get($ip);
            $affId = AffiliateService::getAttributeByPriority($request->get('aff_id'), $request->get('affid'));
            $affId = AffiliateService::validateAffiliateID($affId) ? $affId : null;
            $data = [
                'affiliate' => $affId,
                'offer' => $request->get('offer_id') ? $request->get('offer_id') : ($request->get('offerid') ? $request->get('offerid') : null),
                'url' => $url,
                'page' => $page,
                'fingerprint' => $request->get('f'),
                'price_set' => $priceSet,
                'ip' => $ip,
                'country' => isset($location->countryCode) ? strtolower($location->countryCode) : null

            ];

            Click::saveByData($data);

        } else {
            //logger()->warning('FingerprintWrongData', ['request' => $request->all()]);
        }
    }

    /**
     * Validate affiliate ID
     * @param string $id
     * @return bool
     */
    public static function validateAffiliateID(?string $id): bool
    {
        $valid = false;
        if ($id) {
            $id = trim($id);
            if (in_array($id, AffiliateSetting::$approvedNames)) {
                $valid = true;
            }

            if ((string)(int)$id === (string)$id && strlen($id) > 0 && strlen($id) <= AffiliateSetting::AFFILIATE_ID_LENGTH) {
                $valid = true;
            }
        }

        return $valid;
    }

    /**
     * Replace params aff_subX logic
     * @param array $params
     * @return array
     */
    public static function replaceAffSubsParams(array $params): array
    {
        if (!empty($params['aff_sub1']) && empty($params['aff_sub'])) {
            $params['aff_sub'] = $params['aff_sub1'];
        } elseif (empty($params['aff_sub1']) && !empty($params['aff_sub'])) {
            $params['aff_sub1'] = $params['aff_sub'];
        }
        return $params;
    }
}
