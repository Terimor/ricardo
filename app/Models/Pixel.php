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

    const TYPE_SALE  = 'sale';
    const TYPE_VIEW  = 'view';

    /**
     * Return pixels by params
     * @param string $hoAffiliateID
     * @param type $product
     * @param string $countryCode
     * @param string $route
     * @param string $device
     * @param string $affiliateId
     */
    public static function getPixels($product, string $countryCode, string $route, string $device, string $affiliateId)
    {
        $pixels = Pixel::
            // if we have empty product_ids it's mean ALL
            where(function ($query) use ($product) {
                $query->where('product_ids', '=', $product->id)
                      ->orWhere('product_ids', '=', '');
            }) // if we have empty placements it's mean on ALL pages
            ->where(function ($query) use ($route) {
                $query->where('placements', '=', $route)
                      ->orWhere('placements', '=', '');
            }) // if we have empty device it's mean on ALL devices
            ->where(function ($query) use ($device) {
                $query->where('devices', '=', $device)
                      ->orWhere('devices', '=', '');
            }) // if we have empty countries it's mean on ALL countries
            ->where(function ($query) use ($countryCode) {
                $query->where('countries', '=', $countryCode)
                      ->orWhere('countries', '=', '');
            })->where(function ($query) use ($affiliateId) {
                $query->where('ho_affiliate_id', '=', $affiliateId)
                    ->orWhere('ho_affiliate_id', '=', AffiliateSetting::ALL_INCLUDE_INTERNAL_OPTION)
                    ->orWhere('ho_affiliate_id', '=', AffiliateSetting::ALL_EXCLUDE_INTERNAL_OPTION);
            })
            ->get();
        return $pixels;
    }

}
