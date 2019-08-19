<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Services\CurrencyService;

/**
 * Class OdinProduct
 * @package App\Models
 */
class OdinProduct extends Model
{
    
    protected $fillable = [
        'product_name', 'description', 'long_name', 'is_digital', 'is_hidden_checkout', 'logo_image_id', 'billing_descriptor', 'qty_default', 'is_shipping_cost_only', 'is_3ds_required', 'is_hygiene', 'is_bluesnap_hidden', 'is_paypal_hidden', 'category_id', 'vimeo_id', 'warehouse_id', 'warranty_percent', 'skus', 'prices', 'fb_pixel_id', 'gads_retarget_id', 'gads_conversion_id', 'gads_conversion_label', 'upsell_plusone_text', 'upsell_hero_text', 'upsell_hero_image_id', 'upsells', 'currency'
    ];
    
    
    /**
     * @var string
     */
    protected $collection = 'odin_product';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logoImage()
    {
        return $this->belongsTo(AwsImage::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function upsellHeroImage()
    {
        return $this->belongsTo(AwsImage::class);
    }

    /**
     * Getter description
     */
    public function getDescriptionAttribute($value)
    {
        return !empty($value[app()->getLocale()]) ? $value[app()->getLocale()] : !empty($value['en']) ? $value['en'] : '';
    }

    /**
     * Getter logo_name
     */
    public function getLongNameAttribute($value)
    {
        return !empty($value[app()->getLocale()]) ? $value[app()->getLocale()] : !empty($value['en']) ? $value['en'] : '';
    }

    /**
     * Getter vimeo_id
     */
    public function getVimeoIdAttribute($value)
    {
        return !empty($value[app()->getLocale()]) ? $value[app()->getLocale()] : !empty($value['en']) ? $value['en'] : '';
    }

    /**
     * Getter upsell_plusone_text
     */
    public function getUpsellPlusoneTextAttribute($value)
    {
        return !empty($value[app()->getLocale()]) ? $value[app()->getLocale()] : !empty($value['en']) ? $value['en'] : '';
    }

    /**
     * Getter upsell_hero_text
     */
    public function getUpsellHeroTextAttribute($value)
    {
        return !empty($value[app()->getLocale()]) ? $value[app()->getLocale()] : !empty($value['en']) ? $value['en'] : '';
    }

    /**
     * Getter skus
     */
    public function getSkusAttribute($value)
    {
        foreach ($value as $key => $val) {
            $value[$key]['name'] = !empty($val['name'][app()->getLocale()]) && $val['name'][app()->getLocale()] ? $val['name'][app()->getLocale()] : !empty($val['name']['en']) ? $val['name']['en'] : '';
            $value[$key]['brief'] = !empty($val['brief'][app()->getLocale()]) ? $val['brief'][app()->getLocale()] : !empty($val['brief']['en']) ? $val['brief']['en'] : '';
        }

        return $value;
    }
    
    /**
     * Getter prices
     * @param type $value
     */
    public function getPricesAttribute($value)
    {        
        if (request()->get('cop_id')) {
            $finded = false;
            foreach ($value as $key => $val) {
                if ($val['price_set'] == request()->get('cop_id')) {
                    $finded = true;
                    for ($i=1; $i<=5; $i++) {
                        if (!empty($val[$i]['value'])) {
                            $value[$key][$i]['local'] = CurrencyService::getLocalPriceFromUsd($val[$i]['value']);
                            $value[$key][$i]['currency'] = $value[$key][$i]['local']['code'];
                        }
                    }
                    return $value[$key];
                }
            }
            
            if (!$finded) {
                logger()->error("Cop id ".request()->get('cop_id')." not finded");
            }
        }

        // else set first by default
        
        $returnValue = [];
        foreach ($value as $key => $val) {
            for ($i=1; $i<=5; $i++) {
                if (!empty($val[$i]['value'])) {
                    $value[$key][$i]['local'] = CurrencyService::getLocalPriceFromUsd($val[$i]['value']);
                    $value[$key][$i]['currency'] = $value[$key][$i]['local']['code'];
                }
            }
            break;
        }
   
        return $value[0];
    }
}
