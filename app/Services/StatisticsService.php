<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Click;
use App\Models\OdinProduct;
use App\Models\Domain;
use App\Services\AffiliateService;
use App\Services\ProductService;


/**
 * Statistics Service class
 */
class StatisticsService
{
  /**
   * Prepare data to save to click table
   * @param Request $request
   */
  public function fingerprintClick(Request $request)
  {
      $url = $request->get('page');
      $parsedUrl = parse_url($url);
      
      $page = null;
      if (isset($parsedUrl['path'])) {
          $path = explode('/', $parsedUrl['path']);
          if (isset(Click::$pages[strtolower($path[1])])) {
             $page = $path[1];
          }
      }
      if ($page && $request->get('f')) {
          $priceSet = null;
          // check exists by cop_id parameter
          if ($request->get('cop_id')) {
              $product = OdinProduct::getByCopId($request->get('cop_id'), true);
              if ($product) {
                  $priceSet = $request->get('cop_id');
              }
          }
          // check by product parameter
          if (!$priceSet && $request->get('product')) {
            $product = OdinProduct::getBySku($request->get('product'), false);
            if ($product) {
                $prices = $product['prices'];
                $priceSet = $prices['price_set'] ?? $price_set;
            }            
          }
          
          // check by domain
          if (!$priceSet) {
            $domain = Domain::getByName();           
            if ($domain && !empty($domain->product)) {
                $product =  $domain->product;
                if ($product) {
                    $prices = $product['prices'];
                    $priceSet = $prices['price_set'] ?? $price_set;
                }
            }           
          }
          
     
        $ip = $request->ip();
        $location = \Location::get($ip);
        
        $data = [
            'affiliate' => AffiliateService::getAttributeByPriority($request->get('aff_id'), $request->get('affid')),
            'offer' => $request->get('offer_id') ? $request->get('offer_id') : ($request->get('offerid') ? $request->get('offerid') : null),
            'url' => $url,
            'page' => $page,
            'fingerprint' => $request->get('f'),
            'price_set' => $priceSet,
            'ip' => $ip,
            'country' => isset($location['countryCode']) ? strtolower($location['countryCode']) : null
            
        ];
      
        Click::saveByData($data);
        
      } else {
          logger()->warning('FingerprintWrongData', ['request' => $request->all()]);
      }
  }
}
