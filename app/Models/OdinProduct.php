<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Services\CurrencyService;
use NumberFormatter;

/**
 * Class OdinProduct
 * @package App\Models
 */
class OdinProduct extends Model
{
    const QUANTITY_PRICES = 5;

    protected $images;
    protected $upsellPrices;

    protected $fillable = [
        'product_name', 'description', 'long_name', 'is_digital', 'is_hidden_checkout', 'logo_image_id', 'billing_descriptor', 'qty_default',
    'is_shipping_cost_only', 'is_3ds_required', 'is_hygiene', 'is_bluesnap_hidden', 'is_paypal_hidden', 'category_id', 'vimeo_id',
    'warehouse_id', 'warranty_percent', 'skus', 'prices', 'fb_pixel_id', 'gads_retarget_id', 'gads_conversion_id', 'gads_conversion_label',
    'upsell_plusone_text', 'upsell_hero_text', 'upsell_hero_image_id', 'upsells', 'currency', 'image_ids'
    ];

    protected $hidden = [
        '_id', 'warehouse_id', 'fb_pixel_id', 'gads_retarget_id', 'gads_conversion_id', 'gads_conversion_label', 'created_at', 'updated_at', 'image_id',
    'logo_image_id', 'vimeo_id', 'upsell_hero_image_id', 'category_id', 'is_digital', 'is_hidden_checkout', 'is_shipping_cost_only', 'is_3ds_required',
        'is_hygiene', 'is_bluesnap_hidden', 'is_paypal_hidden'
    ];

    /**
     * @var string
     */
    protected $collection = 'odin_product';

    protected $appends = ['image'];

    protected $attributes = ['image'];

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
        return !empty($value[app()->getLocale()]) ? $value[app()->getLocale()] : (!empty($value['en']) ? $value['en'] : '');
    }

    /**
     * Getter logo_name
     */
    public function getLongNameAttribute($value)
    {
        return !empty($value[app()->getLocale()]) ? $value[app()->getLocale()] : (!empty($value['en']) ? $value['en'] : '');
    }

    /**
     * Getter vimeo_id
     */
    public function getVimeoIdAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter upsell_plusone_text
     */
    public function getUpsellPlusoneTextAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter upsell_hero_text
     */
    public function getUpsellHeroTextAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter skus
     */
    public function getSkusAttribute($value)
    {
        foreach ($value as $key => $val) {
            $value[$key]['name'] = $this->getFieldLocalText($val['name']);
            $value[$key]['brief'] = $this->getFieldLocalText($val['brief']);

            // images
            $value[$key]['quantity_image'] = [];
            for ($i = 1; $i <= self::QUANTITY_PRICES; $i++) {
                if (!empty($value[$key]['quantity_image_ids'][$i])) {
                    $imgId = $value[$key]['quantity_image_ids'][$i];
                    $value[$key]['quantity_image'][$i] = ($imgId && !empty($this->images[$imgId])) ? $this->images[$imgId] : null;
                }
            }
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

      //iteration by price sets array
        foreach ($value as $key => $priceSet) {
            $oneItemPrice = 0;

            //iteration by items quantity for selected price set
            for ($quantity = 1; $quantity <= self::QUANTITY_PRICES; $quantity++) {
                if (!empty($priceSet[$quantity]['value'])) {
                    // val for calculate upsell
                    $value[$key][$quantity]['val'] = $priceSet[$quantity]['value'];
                    $price = CurrencyService::getLocalPriceFromUsd($priceSet[$quantity]['value'], $currency);
                    $value[$key][$quantity]['value'] = $price['price'];
                    $value[$key][$quantity]['value_text'] = $price['price_text'];

                    if ($quantity == 1) {
                        //save one item price
                        $oneItemPrice = $price['price'];
                    }

                    $numberFormatter = new NumberFormatter($currency->localeString, NumberFormatter::CURRENCY);

                    $value[$key][$quantity]['unit_value_text'] = $numberFormatter->formatCurrency($price['price'] / $quantity, $currency->code);

                    $oldPriceValue = CurrencyService::getOldPrice($oneItemPrice, $quantity);
                    $value[$key][$quantity]['old_value_text'] = $numberFormatter->formatCurrency($oldPriceValue, $currency->code);

                    $value[$key][$quantity]['discount_percent'] = CurrencyService::getDiscountPercent($oldPriceValue, $price['price']);

                    if (!empty($this->warranty_percent)) {
                      $warranty_price = floor(($this->warranty_percent / 100) * $price['price'] * 100)/100;
                      $value[$key][$quantity]['warranty_price'] = $warranty_price;
                      $value[$key][$quantity]['warranty_price_text'] = $numberFormatter->formatCurrency($warranty_price, $currency->code);
                      $installments3_warranty_price = CurrencyService::getInstallmentPrice($warranty_price, 3);
                      $installments6_warranty_price = CurrencyService::getInstallmentPrice($warranty_price, 6);
                      $value[$key][$quantity]['installments3_warranty_price_text'] = $numberFormatter->formatCurrency($installments3_warranty_price, $currency->code);
                      $value[$key][$quantity]['installments6_warranty_price_text'] = $numberFormatter->formatCurrency($installments6_warranty_price, $currency->code);
                    } else {
                      $value[$key][$quantity]['warranty_price'] = 0;
                      $value[$key][$quantity]['warranty_price_text'] = null;
                      $value[$key][$quantity]['installments3_warranty_price_text'] = null;
                      $value[$key][$quantity]['installments6_warranty_price_text'] = null;
                    }

                        //installments
                    $installments3_value = CurrencyService::getInstallmentPrice($price['price'], 3);
                    $installments3_old_value = CurrencyService::getInstallmentPrice($oldPriceValue, 3);
                    $installments6_value = CurrencyService::getInstallmentPrice($price['price'], 6);
                    $installments6_old_value = CurrencyService::getInstallmentPrice($oldPriceValue, 6);

                    $value[$key][$quantity]['installments3_value_text'] = $numberFormatter->formatCurrency($installments3_value, $currency->code);
                    $value[$key][$quantity]['installments3_unit_value_text'] = $numberFormatter->formatCurrency($installments3_value / $quantity, $currency->code);
                    $value[$key][$quantity]['installments3_old_value_text'] = $numberFormatter->formatCurrency($installments3_old_value, $currency->code);
                    $value[$key][$quantity]['installments6_value_text'] = $numberFormatter->formatCurrency($installments6_value, $currency->code);
                    $value[$key][$quantity]['installments6_unit_value_text'] = $numberFormatter->formatCurrency($installments6_value / $quantity, $currency->code);
                    $value[$key][$quantity]['installments6_old_value_text'] = $numberFormatter->formatCurrency($installments6_old_value, $currency->code);
                  } else {
                    logger()->error("No prices for quantity {$quantity} of {$this->product_name}");
                  }
                }
                $value[$key]['currency'] = $currency->code;
                $value[$key]['exchange_rate'] = $currency->usd_rate;

                if (!request()->has('cop_id') || $priceSet['price_set'] == request()->get('cop_id')) {
                  $returnedKey = $key;
                  break;
                }
        }

        if (request()->has('cop_id')) {
            logger()->error("Invalid cop_id ".request()->get('cop_id')." for {$this->product_name}");
        }

        return !empty($value[$returnedKey]) ? $value[$returnedKey] : $value;
    }

    /**
     * Set local images for object
     */
    public function setLocalImages($isUpsell = false)
    {
        // get all images ids
        $ids = [];
        if(!empty($this->logo_image_id)) {
            $ids[$this->logo_image_id] = $this->logo_image_id;
        }
        if(!empty($this->upsell_hero_image_id)) {
            $ids[$this->upsell_hero_image_id] = $this->upsell_hero_image_id;
        }

        // Product images
        if (!empty($this->attributes['image_ids'])) {
            foreach($this->attributes['image_ids'] as $imgId) {
                $ids[$imgId] = $imgId;
                if ($isUpsell) {
                    break;
                }
            }
        }

        //for skus
        if (!$isUpsell) {
            if (!empty($this->attributes['skus'])) {
                foreach ($this->attributes['skus'] as $key => $sku) {
                    for ($i = 1; $i <= self::QUANTITY_PRICES; $i++) {
                        if (!empty($sku['quantity_image_ids'][$i])) {
                            $ids[$sku['quantity_image_ids'][$i]] = $sku['quantity_image_ids'][$i];
                        }
                    }
                }
            }
        }
        if ($ids) {
            $this->images = [];
            $this->imagesObjects = AwsImage::whereIn('_id', $ids)->get();
            foreach ($this->imagesObjects as $image) {
                $this->images[$image->id] = !empty($image['urls'][app()->getLocale()]) ? \Utils::replaceUrlForCdn($image['urls'][app()->getLocale()]) : (!empty($image['urls']['en']) ? \Utils::replaceUrlForCdn($image['urls']['en']) : '');
            }
        }
    }

    /**
     * Getter logo image
     */
    public function getLogoImageAttribute($value)
    {
        return (!empty($this->logo_image_id) && !empty( $this->images[$this->logo_image_id])) ? $this->images[$this->logo_image_id] : null;
    }

    /**
     * Getter upsell_hero_image
     */
    public function getUpsellHeroImageAttribute($value)
    {
        return (!empty($this->upsell_hero_image_id) && !empty($this->images[$this->upsell_hero_image_id])) ? $this->images[$this->upsell_hero_image_id] : null;
    }

    /**
     * Getter billing descriptor
     * @param type $value
     */
    public function getBillingDescriptorAttribute($value)
    {
        $billingDescriptorPrefix = \Utils::getSetting('billing_descriptor_prefix');
        return "*{$billingDescriptorPrefix}*{$value}";
    }

    /**
     * Return image
     *
     * @return array
     */
    public function getImageAttribute(): array
    {
        $images = collect($this->image_ids)->map(function($item) {
            if (!empty($this->images[$item])) {
                return $this->images[$item];
            }
            return false;
        })->toArray();

        return $images;
    }

    /**
     * Set upsell prices
     * @param float $fixedPrice
     * @param float $discountPercent
     * @param int $maxQuantity
     * @return boolean
     */
    public function setUpsellPrices(float $fixedPrice = null, float $discountPercent = null, $maxQuantity = self::QUANTITY_PRICES)
    {
        $currency = CurrencyService::getCurrency();

        // if null set quantity 1
        if (!$maxQuantity) {
          $maxQuantity = 1;
        }

        // max 5
        if ($maxQuantity > self::QUANTITY_PRICES) {
          $maxQuantity = self::QUANTITY_PRICES;
        }

        if (!$fixedPrice && !$discountPercent) {
          return false;
        }

        if ($fixedPrice) {
            // quantity loop
            $localPrice = CurrencyService::getLocalPriceFromUsd($fixedPrice, $currency);
            // calculate discount percent
            $priceOld = !empty($this->prices[1]['value']) ? $this->prices[1]['value'] : null;
            $this->attributes['upsellPrices']['discount_percent'] = CurrencyService::getDiscountPercent($priceOld, $localPrice['price']);
            for ($i=1; $i <= $maxQuantity; $i++) {
                $this->attributes['upsellPrices'][$i]['price'] = $localPrice['price'] * $i;
                $this->attributes['upsellPrices'][$i]['price_text'] = CurrencyService::getLocalTextValue($localPrice['price'] * $i, $currency);
                $this->attributes['upsellPrices'][$i]['code'] = $localPrice['code'];
                $this->attributes['upsellPrices'][$i]['exchange_rate'] = $localPrice['exchange_rate'];

            }
        } else if ($discountPercent) {
            // get price from 1 qty
            $discountPrice = !empty($this->prices[1]['val']) ? $this->prices[1]['val'] : null;
            if ($discountPrice) {
              $discountPrice = $discountPrice - ($discountPercent/100 * $discountPrice);
              if ($discountPrice < 4.5) {
                logger()->error("Discount Price < 4.5", ['product' => $this->toArray(), 'discountPercent' => $discountPercent, 'discountPrice' => $discountPrice]);
                $discountPrice = 4.5;
              }
            }

          // quantity loop
          // calculate for qty 1
          $discountLocalPrice = CurrencyService::getLocalPriceFromUsd($discountPrice, $currency);
          $this->attributes['upsellPrices']['discount_percent'] = $discountPercent;
          for ($i=1; $i <= $maxQuantity; $i++) {
            //$price = CurrencyService::getLocalTextValue($discountLocalPrice['price'] * 1, $currency);
            $this->attributes['upsellPrices'][$i]['price'] = $discountLocalPrice['price']*$i;
            $this->attributes['upsellPrices'][$i]['price_text'] = CurrencyService::getLocalTextValue($discountLocalPrice['price'] * $i, $currency);
            $this->attributes['upsellPrices'][$i]['code'] = $discountLocalPrice['code'];
            $this->attributes['upsellPrices'][$i]['exchange_rate'] = $discountLocalPrice['exchange_rate'];
          }
        }

        return true;
    }

    /**
     * Fet field local text
     * @param type $value
     * @return type
     */
    public function getFieldLocalText($value)
    {
        return !empty($value[app()->getLocale()]) ? $value[app()->getLocale()] : (!empty($value['en']) ? $value['en'] : '');
    }
}
