<?php

namespace App\Models;

use Cache;
use NumberFormatter;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Services\CurrencyService;
use App\Services\PaymentService;
use App\Exceptions\ProductNotFoundException;
use App\Models\Setting;

/**
 * Class OdinProduct
 * @package App\Models
 */
class OdinProduct extends Model
{
    const QUANTITY_PRICES = 5;
    const MIN_PRICE = 4.5;

    protected $images;
    protected $upsellPrices;
    public $currency;
    public $currencyObject;
    public $hide_cop_id_log = false;

    protected $fillable = [
        'product_name', 'description', 'long_name', 'home_description', 'home_name', 'is_digital', 'is_hidden_checkout',
        'logo_image_id', 'favicon_image_id', 'billing_descriptor', 'qty_default', 'is_shipping_cost_only',
        'is_3ds_required', 'is_hygiene', 'is_bluesnap_hidden', 'is_paypal_hidden', 'is_choice_required', 'category_id', 'vimeo_id',
        'warehouse_id', 'warranty_percent', 'skus', 'prices', 'fb_pixel_id', 'gads_retarget_id', 'gads_conversion_id',
        'gads_conversion_label', 'upsell_plusone_text', 'upsell_hero_text', 'upsell_hero_image_id', 'upsells', 'reviews', 'affiliates', 'currency',
        'image_ids', 'splash_description', 'reduce_percent', 'is_europe_only', 'is_catch_all_hidden', 'countries', 'reducings', 'price_correction_percents'
    ];

    protected $hidden = [
        '_id', 'warehouse_id', 'fb_pixel_id', 'gads_retarget_id', 'gads_conversion_id', 'gads_conversion_label', 'created_at', 'updated_at',
        'image_id', 'logo_image_id', 'vimeo_id', 'upsell_hero_image_id', 'category_id', 'is_digital', 'is_hidden_checkout', 'is_shipping_cost_only',
        'is_3ds_required', 'is_hygiene', 'is_bluesnap_hidden', 'is_paypal_hidden', 'reduce_percent', 'price_correction_percents'
    ];

    /**
     * @var string
     */
    protected $collection = 'odin_product';

    protected $appends = ['image'];

    protected $attributes = ['image'];

    /**
     * Default reviews array
     */
    public $defaultReviews = [
        [
            'name' => 'Claude',
            'text' => 'It was even better than I expected. The #PRODUCTNAME# is indeed extremely convenient! Really portable and the built quality is really good too.',
            'rate' => 5,
            'image' => '/assets/images/review-user.jpg',
        ],
        [
            'name' => 'Claude',
            'text' => 'I had a small issue during my purchase proccess, but their livechat helped me out within only 3 minutes. #PRODUCTNAME# arrived swiftly and nicely packaged. I simply love it!',
            'rate' => 5,
            'image' => '/assets/images/review-user.jpg',
        ],
        [
            'name' => 'Claude',
            'text' => 'was a bit unsure at first. Could it really live up to its promises? But honestly, #PRODUCTNAME# has entirely surpassed my expectations! I can only say: 10/10!',
            'rate' => 5,
            'image' => '/assets/images/review-user.jpg',
        ],
    ];


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
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter logo_name
     */
    public function getLongNameAttribute($value)
    {
        return $this->getFieldLocalText($value);
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
     * Getter reviews
     */
    public function getReviewsAttribute($value)
    {
        if ($value) {
            foreach ($value as $key => $val) {
                $value[$key]['name'] = $this->getFieldLocalText($val['name']);
                $value[$key]['text'] = $this->getFieldLocalText($val['text']);
                $value[$key]['rate'] = $val['rate'];

                $value[$key]['image'] = !empty($this->images[$val['image_id']]) ? $this->images[$val['image_id']] : null;
            }
        } else {
            $value = [];
        }

        return $value;
    }

    /**
     * Getter prices attribute
     * Formation process prices array with local values depend on currency and countries
     * @param array $value
     * @return array
     */
    public function getPricesAttribute($value)
    {
        if ($this->currencyObject) {
            $currency = $this->currencyObject;
        } else {
            $currency = CurrencyService::getCurrency($this->currency ? $this->currency : null);
        }
        $numberFormatter = new NumberFormatter($currency->localeString, NumberFormatter::CURRENCY);
        // country depends on IP
        $userCountry = \Utils::getLocationCountryCode();
        $returnedKey = 0; $priceSetFound = false;

        //iteration by price sets array
        foreach ($value as $key => $priceSet) {
            $oneItemPrice = 0;

            //iteration by items quantity for selected price set
            for ($quantity = 1; $quantity <= self::QUANTITY_PRICES; $quantity++) {
                if (!empty($priceSet[$quantity]['value'])) {
                    // val for calculate upsell
                    $value[$key][$quantity]['val'] = $priceSet[$quantity]['value'];
                    $price = CurrencyService::getLocalPriceFromUsd($priceSet[$quantity]['value'], $currency, $userCountry, $this->price_correction_percents ?? []);
                    $value[$key][$quantity]['value'] = $price['price'];
                    $value[$key][$quantity]['value_text'] = $price['price_text'];

                    if ($quantity == 1) {
                        //save one item price
                        $oneItemPrice = $price['price'];
                    }

                    $value[$key][$quantity]['unit_value_text'] = CurrencyService::formatCurrency($numberFormatter, ($price['price'] / $quantity), $currency);

                    $oldPriceValue = CurrencyService::getOldPrice($oneItemPrice, $quantity);
                    $value[$key][$quantity]['old_value_text'] = CurrencyService::formatCurrency($numberFormatter, $oldPriceValue, $currency);
                    $value[$key][$quantity]['discount_percent'] = CurrencyService::getDiscountPercent($oldPriceValue, $price['price']);

                    // set additional prices (warranty and installments)
                    $value[$key] = $this->calculateAdditionalPrices($value[$key], $price['price'], $oldPriceValue, $quantity, $numberFormatter, $currency);

                    $value[$key][$quantity]['total_amount'] = round($price['price'] + $value[$key][$quantity]['warranty_price'], 2);
                    $value[$key][$quantity]['total_amount_text'] = CurrencyService::formatCurrency($numberFormatter, $value[$key][$quantity]['total_amount'], $currency);

                  } else {
                    logger()->error("No prices for quantity {$quantity} of {$this->product_name}");
                  }
                }
                $value[$key]['currency'] = $currency->code;
                $value[$key]['exchange_rate'] = $currency->usd_rate;

                if (!request()->has('cop_id') || $priceSet['price_set'] == request()->get('cop_id')) {
                  $returnedKey = $key;
                  $priceSetFound = true;
                  break;
                }
        }

        if (request()->get('cop_id') && !$priceSetFound && !$this->hide_cop_id_log && request()->get('cop_id') != '{cop_id}') {
            logger()->warning("Invalid cop_id ".request()->get('cop_id')." for {$this->product_name}", ['URL' => url()->full()]);
        }

        return !empty($value[$returnedKey]) ? $value[$returnedKey] : $value;
    }

    /**
     * Calculate and set additional prices (warranty and installments)
     * @param array $value
     * @param float $price
     * @param float $oldPriceValue
     * @param int $quantity
     * @param $numberFormatter
     * @param $currency
     * @return array
     */
    public function calculateAdditionalPrices(array $value, float $price, float $oldPriceValue, int $quantity, $numberFormatter, $currency)
    {
        if (!empty($this->warranty_percent)) {
            $warranty_price = floor(($this->warranty_percent / 100) * $price * 100)/100;
            $value[$quantity]['warranty_price'] = $warranty_price;
            $value[$quantity]['warranty_price_text'] = CurrencyService::formatCurrency($numberFormatter, $warranty_price, $currency);
            $installments3_warranty_price = CurrencyService::getInstallmentPrice($warranty_price, 3);
            $installments6_warranty_price = CurrencyService::getInstallmentPrice($warranty_price, 6);
            $value[$quantity]['installments3_warranty_price_text'] = CurrencyService::formatCurrency($numberFormatter, $installments3_warranty_price, $currency);
            $value[$quantity]['installments6_warranty_price_text'] = CurrencyService::formatCurrency($numberFormatter, $installments6_warranty_price, $currency);
        } else {
            $value[$quantity]['warranty_price'] = 0;
            $value[$quantity]['warranty_price_text'] = null;
            $value[$quantity]['installments3_warranty_price_text'] = null;
            $value[$quantity]['installments6_warranty_price_text'] = null;
        }

        //installments
        $installments3_value = CurrencyService::getInstallmentPrice($price, 3);
        $installments3_old_value = CurrencyService::getInstallmentPrice($oldPriceValue, 3);
        $installments6_value = CurrencyService::getInstallmentPrice($price, 6);
        $installments6_old_value = CurrencyService::getInstallmentPrice($oldPriceValue, 6);

        $value[$quantity]['installments3_value_text'] = CurrencyService::formatCurrency($numberFormatter, $installments3_value, $currency);
        $value[$quantity]['installments3_unit_value_text'] = CurrencyService::formatCurrency($numberFormatter, $installments3_value / $quantity, $currency);
        $value[$quantity]['installments3_old_value_text'] = CurrencyService::formatCurrency($numberFormatter, $installments3_old_value, $currency);
        $value[$quantity]['installments6_value_text'] = CurrencyService::formatCurrency($numberFormatter, $installments6_value, $currency);
        $value[$quantity]['installments6_unit_value_text'] = CurrencyService::formatCurrency($numberFormatter, $installments6_value / $quantity, $currency);
        $value[$quantity]['installments6_old_value_text'] = CurrencyService::formatCurrency($numberFormatter, $installments6_old_value, $currency);

        $value[$quantity]['installments3_total_amount_text'] = CurrencyService::formatCurrency($numberFormatter, ($installments3_value + ($installments3_warranty_price ?? 0)), $currency);
        $value[$quantity]['installments6_total_amount_text'] = CurrencyService::formatCurrency($numberFormatter, ($installments6_value + ($installments6_warranty_price ?? 0)), $currency);

        return $value;
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
        if (!empty($this->favicon_image_id)) {
            $ids[$this->favicon_image_id] = $this->favicon_image_id;
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

        if (!empty($this->attributes['reviews'])) {
            foreach ($this->attributes['reviews'] as $review) {
                $ids[$review['image_id']] = $review['image_id'];
            }
        }

        if ($ids) {
            $this->images = [];
            $this->imagesObjects = AwsImage::getByIds($ids);
            foreach ($this->imagesObjects as $image) {
                $this->images[$image->id] = !empty($image['urls'][app()->getLocale()]) ? \Utils::replaceUrlForCdn($image['urls'][app()->getLocale()]) : (!empty($image['urls']['en']) ? \Utils::replaceUrlForCdn($image['urls']['en']) : '');
            }
        }
    }

    /**
     * Get local images ids
     */
    public function getLocalMinishopImagesIds()
    {
        $ids = [];
        if(!empty($this->logo_image_id)) {
            $ids[$this->logo_image_id] = $this->logo_image_id;
        }

        // Product images
        if (!empty($this->attributes['image_ids'])) {
            foreach($this->attributes['image_ids'] as $imgId) {
                $ids[$imgId] = $imgId;
                // get only 0 element
                break;
            }
        }
        return $ids;
    }

    /**
     * Getter logo image
     */
    public function getLogoImageAttribute($value)
    {
        return (!empty($this->logo_image_id) && !empty( $this->images[$this->logo_image_id])) ? $this->images[$this->logo_image_id] : null;
    }

    /**
     * Getter favicon_image
     *
     * @param $value
     * @return mixed
     */
    public function getFaviconImageAttribute($value)
    {
        return (!empty($this->favicon_image_id) && !empty( $this->images[$this->favicon_image_id]))
            ? $this->images[$this->favicon_image_id]
            : null;
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
        $billingDescriptorPrefix = Setting::getValue('billing_descriptor_prefix');
        $host = str_replace('www.', '', request()->getHost());
        $value = "/{$host}/{$value}";
        $value = $billingDescriptorPrefix ? "/{$billingDescriptorPrefix}/{$value}" : $value;
        $value = str_replace('//', '/', $value);
        if (strlen($value) >= PaymentService::BILLING_DESCRIPTOR_MAX_LENGTH) {
            $value = substr($value, 0, PaymentService::BILLING_DESCRIPTOR_MAX_LENGTH);
        }
        return $value;
    }

    /**
     * Return payment billing descriptor
     * @param type $countryCode
     * @return string
     */
    public function getPaymentBillingDescriptor($countryCode = null)
    {
        if ($countryCode && in_array(strtolower($countryCode), PaymentService::BILLING_DESCRIPTOR_COUNTRIES)) {
            $value = $this->getOriginal()['billing_descriptor'];
            $value =  '/'.PaymentService::BILLING_DESCRIPTOR_COUNTRIES_CODE.'/'.$value;
        } else {
            $value = $this->billing_descriptor;
        }
        if (strlen($value) >= PaymentService::BILLING_DESCRIPTOR_MAX_LENGTH) {
            $value = substr($value, 0, PaymentService::BILLING_DESCRIPTOR_MAX_LENGTH);
        }
        return $value;
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
        $this->hide_cop_id_log = true;
        if ($this->currencyObject) {
            $currency = $this->currencyObject;
        } else {
            $currency = CurrencyService::getCurrency($this->currency ? $this->currency : null);
        }
        // country depends on IP
        $userCountry = \Utils::getLocationCountryCode();

        // if null set quantity 1
        if (!$maxQuantity) {
          $maxQuantity = 1;
        }

        // max 5
        if ($maxQuantity > self::QUANTITY_PRICES) {
          $maxQuantity = self::QUANTITY_PRICES;
        }

        if (!$fixedPrice && !$discountPercent) {
          logger()->error("Fixed price and discount percent empty {$this->_id}", ['fixedPrice' => $fixedPrice, 'discountPercent' => $discountPercent]);
          return false;
        }

        if ($fixedPrice) {
            // quantity loop
            $discountLocalPrice = CurrencyService::getLocalPriceFromUsd($fixedPrice, $currency, $userCountry, $this->price_correction_percents ?? []);
            // calculate discount percent
            $priceOld = !empty($this->prices[1]['value']) ? $this->prices[1]['value'] : 0;
            $discountPercent = CurrencyService::getDiscountPercent($priceOld, $discountLocalPrice['price']);
        } else if ($discountPercent) {
            // get price from 1 qty
            $discountPrice = $this->prices[1]['val'] ?? null;
            if ($discountPrice) {
              $discountPrice = $discountPrice - ($discountPercent/100 * $discountPrice);
              if ($discountPrice < self::MIN_PRICE) {
                logger()->error("Discount Price < ".self::MIN_PRICE, ['product' => $this->toArray(), 'discountPercent' => $discountPercent, 'discountPrice' => $discountPrice]);
                $discountPrice = self::MIN_PRICE;
              }
            }
            $discountLocalPrice = CurrencyService::getLocalPriceFromUsd($discountPrice, $currency, $userCountry, $this->price_correction_percents ?? []);
        }

        if (empty($discountLocalPrice['price']) || $discountLocalPrice['price'] <= 0) {
            logger()->error("Price is 0 for upsell product {$this->_id}", ['fixedPrice' => $fixedPrice, 'discountPercent' => $discountPercent]);
            return false;
        }

        // quantity loop
        $this->attributes['upsellPrices']['discount_percent'] = $discountPercent;
        for ($i=1; $i <= $maxQuantity; $i++) {
          $this->attributes['upsellPrices'][$i]['price'] = $discountLocalPrice['price']*$i;
          $this->attributes['upsellPrices'][$i]['price_text'] = CurrencyService::getLocalTextValue($discountLocalPrice['price'] * $i, $currency);
          $this->attributes['upsellPrices'][$i]['code'] = $discountLocalPrice['code'];
          $this->attributes['upsellPrices'][$i]['exchange_rate'] = $discountLocalPrice['exchange_rate'];
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

    /**
     * Get virtual page_title attribute
     *
     * @return mixed
     */
    public function getPageTitleAttribute()
    {
        return $this->long_name;
    }

    /**
     * Returns translated home_description attribute
     *
     * @param $value
     * @return string
     */
    public function getHomeDescriptionAttribute($value): string
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Returns translated home_name attribute
     *
     * @param $value
     * @return string
     */
    public function getHomeNameAttribute($value): string
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Returns product by Sku
     * @param  string $sku
     * @return OdinProduct|null
     */
    public static function getBySku(string $sku, bool $throwable = true): ?OdinProduct
    {
        $product = OdinProduct::where('skus.code', $sku)->first();
        if (!$product && $throwable) {
            throw new ProductNotFoundException("Product {$sku} not found");
        }
        return $product;
    }

    /**
     * Get by cop_id
     * @param string $copId - prices.price_set
     * @param bool $isExists - if true check in database else get a model
     */
    public static function getByCopId(string $copId, bool $isExists = false)
    {
        $model = null;
        if ($isExists) {
            $model = OdinProduct::where('prices.price_set', $copId)->exists();
        } else {
            $model = OdinProduct::where('prices.price_set', $copId)->first();
        }
        return $model;
    }

    /**
     * Get by cop_id
     */
    public static function getById(string $productId)
    {
        return OdinProduct::where('_id', $productId)->first();
    }

    /**
     * Retuen array skus -> product
     * SAGA: OdinProduct::getCachedSkuProduct
     */
    public static function getCacheSkusProduct()
    {
        $skus = Cache::get('SkuProduct');

        //disabled because should be generated in Saga daemons
        if (\App::environment() == 'development') {
            if (!$skus) {
                $products = OdinProduct::all();

                $skus = [];
                foreach ($products as $product) {
                    foreach($product->skus as $sku) {
                      $skus[$sku['code']]['name'] = $sku['name'];
                      $skus[$sku['code']]['product_id'] = $product->id;
                      $skus[$sku['code']]['product_name'] = $product->product_name;
                    }
                }

                Cache::put('SkuProduct', $skus);
            }
        }
        return $skus;
    }

    /**
     * Returns translated splash_description attribute
     *
     * @param array $value
     * @return string
     */
    public function getSplashDescriptionAttribute($value): string
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Get products by ids
     * @param type $ids
     * @param $search
     * @param $hide_catch_all - if true don't return is_catch_all_hidden=true\
     * @param $categoryId
     * @param array|null $select
     */
    public static function getActiveByIds(?array $ids, $search = '', bool $hide_catch_all = false, $categoryId = null, ?array $select = []) {
        $products = null;
        if ($ids) {
            $productsQuery = OdinProduct::whereIn('_id', $ids)->where('skus.is_published', true);
            if ($hide_catch_all) {
                $productsQuery->where(['is_catch_all_hidden' => ['$ne' => true]]);
            }
            if ($search) {
                $productsQuery->where(function ($query) use($search) {
                        $descriptionField = 'description.'.app()->getLocale();
                        $query->where('product_name', 'regexp', "/{$search}/i")
                              ->orWhere($descriptionField, 'regexp', "/{$search}/i");
                    });
            }
            if ($categoryId) {
                $productsQuery->where('category_id', $categoryId);
            }
            if ($select) {
                $productsQuery->select($select);
            }
            $products = $productsQuery->get();
        }
        return $products;
    }

    /**
     * Returns products ids by categories
     * @param array $category_ids
     * @return array
     */
    public static function getProductIdsByCategoryIds(array $category_ids): array
    {
        return OdinProduct::select('_id')
            ->whereIn('category_id', $category_ids)
            ->get()
            ->map(function($v) { return $v->getIdAttribute(); })
            ->all();

    }

    /**
     * Get default product review
     * return array $reviewArray
     */
    public function getDefaultReviews(): array
    {
        $reviewArray = []; $c = 1;
        foreach ($this->defaultReviews as $review) {
            $text = str_replace("#PRODUCTNAME#", $this->product_name, $review['text']);
            $reviewArray[] = [
                'name' => $review['name'],
                'text' => $text,
                'rate' => $review['rate'],
                'image' => \Utils::getCdnUrl().$review['image'],
                'date' => date('M d, Y', strtotime("-{$c} day"))
            ];
            $c++;
        }
        return $reviewArray;
    }
}
