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
    
    const QUANTITY_PRICES = 5;
    public $images;
    
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
        $currency = CurrencyService::getCurrency();

        $returnedKey = 0;
        foreach ($value as $key => $val) {
            for ($i=1; $i <= self::QUANTITY_PRICES; $i++) {
                if (!empty($val[$i]['value'])) {
                    $price = CurrencyService::getLocalPriceFromUsd($val[$i]['value'], $currency);
                    $value[$key][$i]['value'] = $price['price'];
                    $value[$key][$i]['value_text'] = $price['price_text'];
                    if (!empty($val[$i]['image_id'])) {
                        $value[$key][$i]['image'] = $this->images[$val[$i]['image_id']];
                    }
                } else {
                    logger()->error("Price not set for qty {$i} -  ".$this->product_name);
                }
            }
            $value[$key]['currency'] = $currency->code;
            $value[$key]['exchange_rate'] = $currency->usd_rate;
            
            if (!request()->has('cop_id') || $val['price_set'] == request()->get('cop_id')) {
                $returnedKey = $key;
                break;                
            }
        }

        if (request()->has('cop_id')) {
            logger()->error("Cop id ".request()->get('cop_id')." not found");
        }        
   
        return $value[$returnedKey];
    }
    
    /**
     * Set local images for object
     */
    public function setLocalImages()
    {
        // get all images ids
        $ids = [];
        if(!empty($this->logo_image_id)) {
            $ids[$this->logo_image_id] = $this->logo_image_id;
        }
        if(!empty($this->upsell_hero_image_id)) {
            $ids[$this->upsell_hero_image_id] = $this->upsell_hero_image_id;
        }
        
        // for prices
        $returnedKey = 0;
        foreach ($this->attributes['prices'] as $key => $val) {
            if (!request()->has('cop_id') || $val['price_set'] == request()->get('cop_id')) {
                for ($i=1; $i <= self::QUANTITY_PRICES; $i++) {
                    if (!empty($val[$i]['image_id'])) {                        
                        $ids[$val[$i]['image_id']] = $val[$i]['image_id'];                    
                    }
                }
                $returnedKey = $key;
                break;                
            }
        }
        if ($ids) {
            $this->images = [];
            $this->imagesObjects = AwsImage::whereIn('_id', $ids)->get();
            foreach ($this->imagesObjects as $image) {
                $this->images[$image->id] = !empty($image['urls'][app()->getLocale()]) ? $image['urls'][app()->getLocale()] : !empty($image['urls']['en']) ? $image['urls']['en'] : '';                
            }
        }
    }
    
    /**
     * Getter logo image
     */
    public function getLogoImageAttribute($value)
    {
        return !empty($this->logo_image_id) ? $this->images[$this->logo_image_id] : null;
    }
    
    /**
     * Getter upsell_hero_image
     */
    public function getUpsellHeroImageAttribute($value)
    {
        return !empty($this->upsell_hero_image_id) ? $this->images[$this->upsell_hero_image_id] : null;
    }    
}
