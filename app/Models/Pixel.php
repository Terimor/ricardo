<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\AffiliateService;
use App\Services\CurrencyService;

class Pixel extends Model
{
    protected $collection = 'pixel';
    
    protected $dates = ['created_at', 'updated_at'];
    
    public $timestamps = true;    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ho_affiliate_id', 'name', 'type', 'product_ids', 'countries', 'devices', 'placements', 'code', 'is_direct_only'
    ];
    
    const DEVICE_PC = 'pc';
    const DEVICE_TABLET = 'tablet';
    const DEVICE_MOBILE = 'mobile';
    const DEVICE_TV = 'tv';
    
    /**
     * Return array pixel codes
     * @param string $productId
     * @param string $countryCode
     * @param string $route
     * @param string $device
     */
    public static function getPixelsByData(Request $request, $product, string $countryCode, string $route, string $device) : array
    {
        $pixels = Pixel::where(['product_ids' => $product->id, 'countries' => $countryCode, 'placements' => $route, 'devices' => $device])->get();
        
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
            
            $pixelsArray[] = [
                'type' => $pixel->type,
                'code' => $code
            ];                        
        }
        
        return $pixelsArray;        
    }

}
