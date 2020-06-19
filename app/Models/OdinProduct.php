<?php

namespace App\Models;

use Cache;
use Illuminate\Support\Facades\Route;
use NumberFormatter;
use Jenssegers\Mongodb\Eloquent\Model;
use App\Services\CurrencyService;
use App\Services\PaymentService;
use App\Exceptions\ProductNotFoundException;

/**
 * Class OdinProduct
 * @package App\Models
 *
 * @property string type
 * @property string billing_descriptor
 * @property string description
 * @property string long_name
 * @property string logo_image
 * @property string product_name
 * @property string warehouse_id
 */
class OdinProduct extends OdinModel
{
    const VIRTUAL_QUANTITY_PRICES = 1;
    const VIRTUAL_UPSELLS_QUANTITY_PRICES = 3;
    const PHYSICAL_QUANTITY_PRICES = 5;
    const PHYSICAL_UPSELLS_QUANTITY_PRICES = 10;
    const MIN_PRICE = 4.5;

    const TYPE_PHYSICAL = 'physical';
    const TYPE_DIGITAL = 'digital';
    const TYPE_VIRTUAL = 'virtual';

    protected $images;
    protected $upsellPrices;
    public $currency;
    public $currencyObject;
    public $hide_cop_id_log = false;
    public $skip_prices = false; // hide log if we select only prices.price_set and skip prices calculation

    protected $fillable = [
        'type', 'product_name', 'description', 'long_name', 'home_description', 'home_name', 'is_hidden_checkout',
        'logo_image_id', 'favicon_image_id', 'bg_image_id', 'billing_descriptor', 'unit_qty', 'is_shipping_cost_only',
        'is_3ds_required', 'is_hygiene', 'is_bluesnap_hidden', 'is_paypal_hidden', 'is_choice_required', 'category_id', 'vimeo_id',
        'warehouse_id', 'warranty_percent', 'skus', 'prices', 'fb_pixel_id', 'gads_retarget_id', 'gads_conversion_id',
        'gads_conversion_label', 'goptimize_id', 'upsell_plusone_text', 'upsell_hero_text', 'upsell_hero_image_id', 'upsells', 'reviews', 'affiliates', 'currency',
        'image_ids', 'splash_description', 'reduce_percent', 'is_europe_only', 'is_catch_all_hidden', 'countries', 'reducings', 'price_correction_percents'
    ];

    protected $hidden = [
        '_id', 'type', 'warehouse_id', 'fb_pixel_id', 'gads_retarget_id', 'gads_conversion_id', 'gads_conversion_label', 'created_at', 'updated_at',
        'image_id', 'logo_image_id', 'bg_image_id', 'vimeo_id', 'upsell_hero_image_id', 'category_id', 'is_hidden_checkout', 'is_shipping_cost_only',
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
     * Getter upsell_title
     */
    public function getUpsellTitleAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter upsell_subtitle
     */
    public function getUpsellSubtitleAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter upsell_description
     */
    public function getUpsellDescriptionAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter upsell_letter
     */
    public function getUpsellLetterAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter upsell_lcta_title
     */
    public function getUpsellLctaTitleAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter upsell_lcta_description
     */
    public function getUpsellLctaDescriptionAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter upsell_vimeo_id
     */
    public function getUpsellVideoIdAttribute($value)
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
            for ($i = 1; $i <= $this->castPriceQuantity(); $i++) {
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
        // skip prices logic
        $priceSetFound = false;
        $returnedKey = 0;
        if (!$this->skip_prices) {
            if ($this->currencyObject) {
                $currency = $this->currencyObject;
            } else {
                $currency = CurrencyService::getCurrency($this->currency ? $this->currency : null);
            }
            $numberFormatter = new NumberFormatter($currency->localeString, NumberFormatter::CURRENCY);
            // country depends on IP
            $userCountry = \Utils::getLocationCountryCode();
        }

        $oneItemPrice = 0;
        //iteration by price sets array
        foreach ($value as $key => $priceSet) {
            //iteration by items quantity for selected price set
            $quantityPrices = $this->castPriceQuantity();
            for ($quantity = 1; $quantity <= $quantityPrices; $quantity++) {
                // if skip_prices this value is empty
                if (!empty($priceSet[$quantity]['value'])) {
                    $value = $this->preparePricesForQty($value, $key, $priceSet, $quantity, $currency, $userCountry, $numberFormatter, $oneItemPrice);

                  } else {
                    if (!$this->skip_prices) {
                        logger()->error("No prices for quantity {$quantity} of {$this->product_name}");
                    }
                  }
                }
                if (!$this->skip_prices) {
                    $value[$key]['currency'] = $currency->code;
                    $value[$key]['exchange_rate'] = $currency->usd_rate;
                }

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
     * Prepare prices for quantity 1 to N depends on price set
     * @param $value
     * @param $key
     * @param array $priceSet
     * @param int $quantity
     * @param Currency $currency
     * @param string|null $userCountry
     * @param NumberFormatter $numberFormatter
     * @param float $oneItemPrice
     * @return array
     */
    private function preparePricesForQty($value, $key, array $priceSet, int $quantity, Currency $currency, ?string $userCountry, NumberFormatter $numberFormatter, &$oneItemPrice): array
    {
        $unitQty = !empty($this->unit_qty) ? $this->unit_qty : 1;
        // val for calculate upsell
        $value[$key][$quantity]['val'] = $priceSet[$quantity]['value'];
        $price = CurrencyService::getLocalPriceFromUsd($priceSet[$quantity]['value'], $currency, $userCountry, $this->price_correction_percents ?? []);
        $value[$key][$quantity]['value'] = $price['price'];
        $value[$key][$quantity]['value_text'] = $price['price_text'];

        if ($quantity == 1) {
            //save one item price
            $oneItemPrice = $price['price'];
            // 25 percent for splash pages
            if ((Route::is('splashvirtual') || Route::is('splash')) && $this->type === self::TYPE_VIRTUAL) {
                $value[$key]['25p']['value'] = round($price['price'] * 0.25, 2);
                $value[$key]['25p']['value_text'] = CurrencyService::formatCurrency($numberFormatter, $value[$key]['25p']['value'], $currency);
            }
            // 30 percent discount for virtual product
            if ($this->type === self::TYPE_VIRTUAL) {
                $value[$key]['30d']['value'] = round($price['price'] - $price['price'] * 0.3, 2);
                $value[$key]['30d']['value_text'] = CurrencyService::formatCurrency($numberFormatter, $value[$key]['30d']['value'], $currency);
            }
        }

        $value[$key][$quantity]['unit_value_text'] = CurrencyService::formatCurrency($numberFormatter, ($price['price'] / ($quantity * $unitQty)), $currency);

        $oldPriceValue = CurrencyService::getOldPrice($oneItemPrice, $quantity);
        $value[$key][$quantity]['old_value_text'] = CurrencyService::formatCurrency($numberFormatter, $oldPriceValue, $currency);
        $value[$key][$quantity]['discount_percent'] = CurrencyService::getDiscountPercent($oldPriceValue, $price['price']);

        // set additional prices (warranty and installments)
        $value[$key] = $this->calculateAdditionalPrices($value[$key], $price['price'], $oldPriceValue, $quantity, $numberFormatter, $currency);

        $value[$key][$quantity]['total_amount'] = round($price['price'] + $value[$key][$quantity]['warranty_price'], 2);
        $value[$key][$quantity]['total_amount_text'] = CurrencyService::formatCurrency($numberFormatter, $value[$key][$quantity]['total_amount'], $currency);

        return $value;
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
    private function calculateAdditionalPrices(array $value, float $price, float $oldPriceValue, int $quantity, $numberFormatter, $currency)
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
        if(!empty($this->bg_image_id)) {
            $ids[$this->bg_image_id] = $this->bg_image_id;
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
                    for ($i = 1; $i <= $this->castPriceQuantity(); $i++) {
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
     * @return array
     */
    public function getLocalMinishopImagesIds(): array
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
     * @param $value
     * @return string|null
     */
    public function getLogoImageAttribute($value): ?string
    {
        return (!empty($this->logo_image_id) && !empty( $this->images[$this->logo_image_id])) ? $this->images[$this->logo_image_id] : null;
    }

    /**
     * Getter favicon_image
     * @param $value
     * @return string|null
     */
    public function getFaviconImageAttribute($value): ?string
    {
        return (!empty($this->favicon_image_id) && !empty( $this->images[$this->favicon_image_id])) ? $this->images[$this->favicon_image_id] : null;
    }

    /**
     * Getter bg image
     * @param $value
     * @return string|null
     */
    public function getBgImageAttribute($value): ?string
    {
        return (!empty($this->bg_image_id) && !empty( $this->images[$this->bg_image_id])) ? $this->images[$this->bg_image_id] : null;
    }

    /**
     * Getter upsell_hero_image
     * @param $value
     * @return string|null
     */
    public function getUpsellHeroImageAttribute($value): ?string
    {
        return (!empty($this->upsell_hero_image_id) && !empty($this->images[$this->upsell_hero_image_id])) ? $this->images[$this->upsell_hero_image_id] : null;
    }

    /**
     * Getter billing descriptor
     * @param type $value
     * @return string
     */
    public function getBillingDescriptorAttribute($value): string
    {
        $billingDescriptorPrefix = Setting::getValue('billing_descriptor_prefix');
        $host = str_replace('www.', '', request()->getHost());
        $value = "{$host}/{$value}";
        $value = $billingDescriptorPrefix ? "{$billingDescriptorPrefix}/{$value}" : $value;
        $value = str_replace('//', '/', $value);
        if (strlen($value) >= PaymentService::BILLING_DESCRIPTOR_MAX_LENGTH) {
            $value = substr($value, 0, PaymentService::BILLING_DESCRIPTOR_MAX_LENGTH);
        }
        return $value;
    }

    /**
     * Return payment billing descriptor
     * @param string $countryCode
     * @return string
     */
    public function getPaymentBillingDescriptor(string $countryCode): string
    {
        $value = PaymentService::getBillingDescriptorCodeByCountry($countryCode) . '/' . $this->getOriginal('billing_descriptor');
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
     * Returns correct price quantity
     * @param int|null $quantity
     * @return int
     */
    public function castPriceQuantity(?int $quantity = null): int
    {
        if (!$quantity || $quantity < 1) {
            $quantity = $this->type === self::TYPE_PHYSICAL ? self::PHYSICAL_QUANTITY_PRICES : self::VIRTUAL_QUANTITY_PRICES;
        }
        return $quantity;
    }

    /**
     * Set upsell prices
     * @param float $fixedPrice
     * @param float $discountPercent
     * @param int|null $quantity
     * @return boolean
     */
    public function setUpsellPrices(float $fixedPrice = null, float $discountPercent = null, ?int $quantity = null): bool
    {
        $this->hide_cop_id_log = true;
        if ($this->currencyObject) {
            $currency = $this->currencyObject;
        } else {
            $currency = CurrencyService::getCurrency($this->currency ? $this->currency : null);
        }
        // country depends on IP
        $userCountry = \Utils::getLocationCountryCode();

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
        $upsellPrices = [];
        $quantity = $this->castPriceQuantity($quantity);
        for ($i=1; $i <= $quantity; $i++) {
            $upsellPrices[$i]['price'] = $discountLocalPrice['price']*$i;
            $upsellPrices[$i]['price_text'] = CurrencyService::getLocalTextValue($discountLocalPrice['price'] * $i, $currency);
        }
        // for virtual product
        if ($this->type == OdinProduct::TYPE_VIRTUAL) {
            $additionalPrices = [10, 20];
            foreach ($additionalPrices as $i) {
                $upsellPrices[$i]['price'] = $discountLocalPrice['price']*$i;
                $upsellPrices[$i]['price_text'] = CurrencyService::getLocalTextValue($discountLocalPrice['price'] * $i, $currency);
            }
            // 30% discount
            $upsellPrices['30d']['price'] = round($discountLocalPrice['price'] - $discountLocalPrice['price'] * 0.3, 2);
            $upsellPrices['30d']['price_text'] = CurrencyService::getLocalTextValue($upsellPrices['30d']['price'], $currency);
        }
        foreach ($upsellPrices as $key => $value) {
            $upsellPrices[$key]['code'] = $discountLocalPrice['code'];
            $upsellPrices[$key]['exchange_rate'] = $discountLocalPrice['exchange_rate'];
        }
        $upsellPrices['discount_percent'] = $discountPercent;
        $this->attributes['upsellPrices'] = $upsellPrices;

        return true;
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
     * @param string $sku
     * @param bool $throwable
     * @param array $select
     * @return OdinProduct|null
     * @throws ProductNotFoundException
     */
    public static function getBySku(string $sku, bool $throwable = true, $select = []): ?OdinProduct
    {
        $query = OdinProduct::where('skus.code', $sku);
        if ($select) {
            $query->select($select);
        }
        $product = $query->first();

        if (!$product && $throwable) {
            throw new ProductNotFoundException("Product {$sku} not found");
        }
        return $product;
    }

    /**
     * Get by cop_id
     * @param string|null $cop_id - prices.price_set
     * @param array $select
     * @return OdinProduct|null
     */
    public static function getByCopId(?string $cop_id, array $select = []): ?OdinProduct
    {
        $model = null;
        if ($cop_id) {
            $query = OdinProduct::where('prices.price_set', $cop_id);
            if ($select) {
                $query = $query->select($select);
            }
            $model = $query->first();
        }
        return $model;
    }

    /**
     * Get by ID
     * @param string $productId
     * @param array $select
     * @return mixed
     */
    public static function getById(string $productId, $select = [])
    {
        $query = OdinProduct::where('_id', $productId);
        if ($select) {
            $query->select($select);
        }
        return $query->first();
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
                        $skus[$sku['code']]['unit_qty'] = $product->unit_qty ?? 1;
                    }
                }

                Cache::put('SkuProduct', $skus, 600);
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
     * @param $hide_catch_all - if true don't return is_catch_all_hidden=true
     * @param $categoryId
     * @param array|null $select
     * @param string|null $type
     */
    public static function getActiveByIds(?array $ids, $search = '', bool $hide_catch_all = false, $categoryId = null, ?array $select = [], ?string $type = null) {
        $products = null;
        if ($ids) {
            $productsQuery = OdinProduct::whereIn('_id', $ids)->where('skus.is_published', true);
            if ($hide_catch_all) {
                $productsQuery->where(['is_catch_all_hidden' => ['$ne' => true]]);
            }
            if ($type) {
                $productsQuery->where(['type' => $type]);
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
     * Return array of product ids by given type
     * @param string $type
     * @return array
     */
    public static function getProductIdsByType(string $type)
    {
        return OdinProduct::query()
            ->where('type', $type)
            ->where('skus.is_published', true)
            ->select(['_id'])
            ->pluck('_id')
            ->toArray();
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

    /**
     * Check existance by cop_id
     * @param string $cop_id - prices.price_set
     * @return bool
     */
    public static function isExistsByCopId(string $cop_id): bool
    {
        return OdinProduct::where('prices.price_set', $cop_id)->exists();
    }

    /**
     * Get product for display local values
     * Method for ProductService->resolveProduct
     * @param array $where
     * @param array $select
     * @return mixed
     */
    public static function getResolveProductForLocal(array $where, array $select = []) {
        $query = static::where($where);
        if ($select) {
            $query->select($select);
        }
        return $query->first();
    }

    /**
     * Has battery
     * @return bool
     */
    public function hasBattery(): bool {
        $skus = $this->skus ?? null;
        $hasBattery = false;
        if ($skus) {
            foreach ($skus as $sku) {
                if (!empty($sku['has_battery'])) {
                    $hasBattery = $sku['has_battery'];
                    break;
                }
            }
        }
        return $hasBattery;
    }

    /**
     * Getter for has battery
     * @return bool
     */
    public function getHasBatteryAttribute(): bool {
        return $this->hasBattery();
    }

    /**
     * Returns products by sku_codes
     * @param array $skus
     * @param array $select
     * @return null|\Illuminate\Database\Eloquent\Collection
     */
    public static function getBySkus(array $skus, $select = []): ?\Illuminate\Database\Eloquent\Collection
    {
        $products = null;
        if ($skus) {
            $query = OdinProduct::whereIn('skus.code', $skus);
            if ($select) {
                $query->select($select);
            }
            $products = $query->get();
        }
        return $products;
    }
}
