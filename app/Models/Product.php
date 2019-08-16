<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Product
 * @package App\Models
 */
class Product extends Model
{
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
}
