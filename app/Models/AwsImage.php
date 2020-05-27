<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class AwsImage extends OdinModel
{
    protected $collection = 'aws_image';

    protected $dates = ['created_at', 'updated_at'];

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category', 'is_wide', 'name', 'urls', 'title',
    ];

    const CATEGORY_LOGO  = 'logo';
    const CATEGORY_PRODUCT  = 'product';
    const CATEGORY_PAYMENT_METHOD  = 'payment_method';
    const CATEGORY_GLOBAL  = 'global';
    const CATEGORY_PROMO_PAGE  = 'promo_page';

    public static $categories = [
      self::CATEGORY_LOGO => 'Logo',
      self::CATEGORY_PRODUCT => 'Product',
      self::CATEGORY_PAYMENT_METHOD => 'Payment method',
      self::CATEGORY_GLOBAL => 'Global',
      self::CATEGORY_PROMO_PAGE => 'Promo page',
    ];

    /**
    * Returns type as a text
    * @return string
    */
    public function getCategoryText() {
        if (!empty(static::$categories[$this->category])) {
          return static::$categories[$this->category];
        } else {
          return 'Unknown';
        }
    }

    /**
     * Get images by ids
     * @param array $ids
     * @param array $select
     * @return mixed
     */
    public static function getByIds(?array $ids, array $select = []): ?\Illuminate\Database\Eloquent\Collection
    {
        $images = null;
        if ($ids) {
            $select = ['urls.en'];
            $select = app()->getLocale() != 'en' ? \Utils::addLangFieldToSelect($select, app()->getLocale()) : $select;
            $query = AwsImage::whereIn('_id', $ids);
            if ($select) {
                $query->select($select);
            }
            $images = $query->get();
        }
        return $images;
    }

}
