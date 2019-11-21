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
        'name', 'logo', 'odin_product_id', 'ga_id', 'display_name'
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
     public static function getByRequest($request): ?Domain
     {         
         $domain = null;
         // check by cop_id
         if ($request->get('cop_id')) {
             $product = OdinProduct::getByCopId($request->get('cop_id'));
             if ($product) {
                 $domain = static::getByProductId($product->_id);
             }
         }
         
         // check by sku
         if (!$domain && $request->get('product')) {
             $product = OdinProduct::getBySku($request->get('product'));
             if ($product) {
                 $domain = static::getByProductId($product->_id);
             }
         }
         
         if (!$domain) {
            $domain = Domain::getByName();
         }

         return $domain;
     }     
}
