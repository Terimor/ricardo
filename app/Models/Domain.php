<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Category
 * @package App\Models
 */
class Domain extends Model
{
    /**
     * @var string
     */
    protected $collection = 'domain';

    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'logo', 'odin_product_id', 'ga_id', 'display_name', 'sold_products', 'is_multiproduct'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->hasOne(OdinProduct::class, '_id', 'odin_product_id');
    }

    /**
     * Returns domain by name
     *
     * @param null $name
     * @return Domain|null
     */
     public static function getByName($name = null)
     {
         $host = str_replace('www.', '', request()->getHost());
         $name = $name ?? $host;

         return Domain::where('name', $name)->first();
     }
     
    /**
     * Returns domain by product id
     * @param string id
     * @return Domain|null
     */
     public static function getByProductId($id): ?Domain {
         return Domain::where('odin_product_id', (string)$id)->first();
     }
     
    /**
     * Returns domain by request parameters
     * @param array $request
     * @return Domain|null
     */
     public static function getByParams($copId = null, $sku = null): ?Domain
     {         
         $domain = null;
         // check by cop_id
         if ($copId) {
             $product = OdinProduct::getByCopId($copId);
             if ($product) {
                 $domain = static::getByProductId($product->_id);
             }
         }
         
         // check by sku
         if (!$domain && $sku) {
             $product = OdinProduct::getBySku($sku);
             if ($product) {
                 $domain = static::getByProductId($product->_id);
             }
         }
         
         if (!$domain) {
            $domain = Domain::getByName();
         }

         return $domain;
     }     

     /**
     * Set local logo
     */
    public function setLocalLogo()
    {
        if (!isset($this->logo)) {
            return;
        };

        $images = AwsImage::where('_id', $this->logo)->get();

        if (count($images) === 0) {
            return;
        }

        $lang = app()->getLocale();

        $this->logo = isset($images[0]->urls[$lang])
            ? \Utils::replaceUrlForCdn($images[0]->urls[$lang])
            : (isset($images[0]->urls['en'])
                ? \Utils::replaceUrlForCdn($images[0]->urls['en'])
                : '');
    }
    
    /**
     * Returns displayed name     
     * @param array $value
     * @return string
     */
    public function getDisplayedName(): string
    {
        return !empty($this->display_name) ? $this->display_name : $this->name;
    }
}
