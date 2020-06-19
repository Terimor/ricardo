<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Route;
use App\Models\{MediaAccess, OdinProduct, Domain, Currency, AwsImage, PaymentApi, File, Localize, Video};
use Illuminate\Http\Request;
use NumberFormatter;
use Cache;

/**
 * Class ProductService
 * @package App\Services
 */
class ProductService
{
    public static array $amountValues = [10, 15, 20];

    /**
     * @param Request $request
     * @param bool $needImages - if true collect all images
     * @param null $currency
     * @param bool $isPostback
     * @return OdinProduct
     */
    public function resolveProduct(Request $request, $needImages = false, $currency = null, $isPostback = false)
    {
        // prepare select
        $select = ['type', 'prices', 'skus', 'product_name', 'description.en', 'long_name.en', 'home_description.en', 'home_name.en',
            'splash_description.en', 'billing_descriptor', 'logo_image_id', 'bg_image_id', 'favicon_image_id', 'upsell_hero_image_id', 'image_ids',
            'vimeo_id.en', 'reviews', 'upsell_plusone_text.en', 'upsell_hero_text.en', 'upsells', 'fb_pixel_id', 'gads_retarget_id',
            'gads_conversion_id','gads_conversion_label', 'goptimize_id', 'is_europe_only','is_choice_required','is_paypal_hidden','countries',
            'labels.1.en', 'labels.2.en', 'labels.3.en', 'labels.4.en', 'labels.5.en', 'warehouse_id', 'warranty_percent',
            'price_correction_percents', 'unit_qty', 'is_discount', 'is_hygiene', 'free_file_ids'];

        // add .lang
        $select = app()->getLocale() != 'en' ? \Utils::addLangFieldToSelect($select, app()->getLocale()) : $select;

        $domain = Domain::getByName();
        $product = $this->getResolveProductByRequest($request, $domain, $select);

        // set local images
        if ($needImages) {
            $product->setLocalImages();
        }

        if ($currency) {
            $product->currency = $currency;
        }

        if (!empty($domain->is_multiproduct) || !empty($domain->is_catch_all)) {
            $product->hide_cop_id_log = true;
        }

        $localizedProduct = $this->localizeProduct($product);
        $localizedProduct->id = $product->id;

        if ($isPostback) {
            // price set
            $prices = $product->prices;
            $localizedProduct->price_set = !empty($prices['price_set']) ? $prices['price_set'] : null;
        }

        return $localizedProduct;
        //abort(404);
    }

    /**
     * Get resolve product by Request
     * @param Request $request
     * @param null|Domain $domain
     * @param array $select
     * @return OdinProduct|null
     */
    public function getResolveProductByRequest(Request $request, ?Domain $domain, array $select = []): ?OdinProduct {
        $product = null;
        if ($request->has('cop_id')) {
            $product = OdinProduct::getResolveProductForLocal(['prices.price_set' => $request->input('cop_id')], $select);
        }

        if (!$product && $request->has('product')) {
            $product = OdinProduct::getResolveProductForLocal(['skus.code' => $request->input('product')], $select);
        }

        // Domain resolve logic
        if (!$product) {
            if ($domain && !empty($domain->odin_product_id)) {
                $product = OdinProduct::getResolveProductForLocal(['_id' => $domain->odin_product_id], $select);
            }
        }

        if (!$product) {
            logger()->error("Can't find a product", ['request' => $request->all(), 'domain' => $domain]);
            $product = OdinProduct::orderBy('_id', 'desc')->where('skus.is_published', true)->firstOrFail();
        }
        return $product;
    }

    /**
     * Get resolve product for upsell
     * @param Request $request
     * @return OdinProduct|null
     */
    public function resolveProductForUpsell(Request $request): ?OdinProduct {
        $select = ['upsells', 'type'];
        $domain = Domain::getByName();
        return $this->getResolveProductByRequest($request, $domain, $select);
    }

    /**
     * Get upsell product by ID
     *
     * @param OdinProduct $product
     * @param string $productId
     * @param int|null $quantity
     * @param string|null $currency
     * @return OdinProduct
     */
    public function getUpsellProductById($product, string $productId, ?int $quantity = null, ?string $currency = null)
    {
        // check upsell product by ID
        $productUpsells = !empty($product->upsells) ? $product->upsells : null;
        if (!$productUpsells) {
            logger()->error("Can't find a upsell products", ['request' => request()->all(), 'productId' => $productId, 'product' => $product->toArray()]);
            abort(404);
        }

        // check product upsells and get dicounts/prices for setUpsellPrices
        $upsell = null;
        foreach ($productUpsells as $uproduct) {
            if ($uproduct['product_id'] == $productId) {
                $fixedPrice = !empty($uproduct['fixed_price']) ? $uproduct['fixed_price'] : null;
                $discountPercent = !empty($uproduct['discount_percent']) ? $uproduct['discount_percent'] : null;

                $select = ['product_name', 'description.en', 'long_name.en', 'billing_description', 'logo_image_id', 'upsell_hero_image_id', 'skus',
                    'image_ids', 'prices', 'price_correction_percents', 'warranty_percent', 'upsell_plusone_text.en', 'unit_qty', 'type'];
                // get virtual fields
                if ($product->type == OdinProduct::TYPE_VIRTUAL) {
                    $select = array_merge($select, ['upsell_title.en','upsell_subtitle.en', 'upsell_description.en', 'upsell_letter.en',
                        'upsell_lcta_title.en', 'upsell_lcta_description.en', 'upsell_video_id.en']);
                }

                if (app()->getLocale() != 'en') {
                    $select = array_merge($select, ['description.'.app()->getLocale(),'long_name.'.app()->getLocale(), 'upsell_plusone_text.'.app()->getLocale()]);
                }
                $upsell = OdinProduct::getById($productId, $select);
            }
        }

        if (!$upsell) {
            logger()->error("Can't find a upsell products", ['request' => request()->all(), 'productId' => $productId, 'product' => $product->toArray()]);
            abort(404);
        }

        if (!$fixedPrice && !$discountPercent) {
            logger()->error("fixedPrice and discountPercent is null for productID {$product->id}", ['product' => $product, 'upsell_id' => $productId]);
            abort(409);
        }

        if ($fixedPrice && $fixedPrice < OdinProduct::MIN_PRICE) {
            $fixedPrice = OdinProduct::MIN_PRICE;
            logger()->error("UPSELL Price < ".OdinProduct::MIN_PRICE, ['product' => $product->toArray()]);
        }

        if (!$upsell->skus) {
            logger()->error("UPSELL skus empty or not published", ['product' => $product->toArray()]);
            abort(405, 'Method Not Allowed');
        }

        if ($currency) {
            $upsell->currency = $currency;
        }

        $upsell->setUpsellPrices($fixedPrice, $discountPercent, $quantity);

        return $upsell;
    }

    /**
     * @param $product
     * @return array
     */
    public function getProductUpsells($product): array
    {
        $productUpsells = !empty($product->upsells) ? $product->upsells : null;
        $productIds = $productUpsellsArray = [];
        if ($productUpsells) {
            $currency = CurrencyService::getCurrency();
            foreach ($productUpsells as $upsell) {
                $productIds[] = $upsell['product_id'];
            }

            // select data only in use
            $select = ['type','logo_image_id', 'image_ids', 'prices', 'skus.code', 'skus.is_published', 'skus.name.en', 'skus.brief.en', 'price_correction_percents', 'warranty_percent'];
            if (app()->getLocale() != 'en') {
                $select[] = 'skus.name.'.app()->getLocale();
                $select[] = 'skus.brief.'.app()->getLocale();
            }
            $products = OdinProduct::getActiveByIds($productIds, '', false, null, $select);

            // collect images_ids
            $imagesArray = [];
            foreach ($products as $product) {
                if (!empty($product->logo_image_id)) {
                    $imagesArray[$product->logo_image_id] = $product->logo_image_id;
                }
                if (!empty($product->image_ids[0])) {
                    $imagesArray[$product->image_ids[0]] = $product->image_ids[0];
                }
            }

            $images = AwsImage::getByIds(array_values(array_unique($imagesArray)));
            if ($images) {
                foreach ($images as $image) {
                    if (isset($imagesArray[(string)$image->_id])) {
                        $imagesArray[(string)$image->_id] = !empty($image['urls'][app()->getLocale()]) ? \Utils::replaceUrlForCdn($image['urls'][app()->getLocale()]) : (!empty($image['urls']['en']) ? \Utils::replaceUrlForCdn($image['urls']['en']) : '');;
                    }
                }
            }

            foreach ($productUpsells as $uproduct) {
                foreach ($products as $pr) {
                    $pr->currencyObject = $currency;
                    if ($uproduct['product_id'] == (string)$pr->_id) {
                        $fixedPrice = !empty($uproduct['fixed_price']) ? $uproduct['fixed_price'] : null;
                        $discountPercent = !empty($uproduct['discount_percent']) ? $uproduct['discount_percent'] : null;
                        $pr->setUpsellPrices($fixedPrice, $discountPercent, 2);
                        $productUpsellsArray[] = $this->localizeCheckoutUpsell($pr, $imagesArray);
                    }
                }
            }
        }
        return $productUpsellsArray;
    }

    /**
     * Calculate upsells total
     * @param $product
     * @param array $upsells
     * @param bool $with_extra
     * @param string|null $currency_code
     * @return array
     */
    public function calculateUpsellsTotal($product, array $upsells, bool $with_extra = false, ?string $currency_code = null) : array
    {
        // TODO: modify WHERE IN
        $totalSumCalc = 0;
        $exchange_rate = null;
        foreach ($upsells as $id => $quantity) {
            $upsellProduct = $this->getUpsellProductById($product, $id, $quantity, $currency_code);
            $totalSumCalc += !empty($upsellProduct->upsellPrices[$quantity]['price']) ? $upsellProduct->upsellPrices[$quantity]['price'] : 0;
            $currency_code = $upsellProduct->upsellPrices[$quantity]['code'];
            $exchange_rate = $upsellProduct->upsellPrices[$quantity]['exchange_rate'];
        }

        $extra_result = [
            'value' => $totalSumCalc,
            'code' => $currency_code,
            'exchange_rate' => $exchange_rate
        ];
        $result = [
            'value_text' => CurrencyService::getLocalTextValue($totalSumCalc),
        ];
        $result = $with_extra ? array_merge($result, $extra_result) : $result;

        return $result;

    }

    /**
     *
     * @param OdinProduct $product
     * @return stdClass
     */
    public function localizeProduct($product)
    {
        // prepare localized product
        $lp = new Localize();
        $lp->type = $product->type;
        $lp->product_name = $product->product_name;
        $lp->description = $product->description;
        $lp->long_name = $product->long_name;
        $lp->home_description = $product->home_description;
        $lp->home_name = $product->home_name;
        $lp->splash_description = $product->splash_description;
        $lp->billing_descriptor = $product->billing_descriptor;
        $lp->logo_image = $product->logo_image;
        $lp->bg_image = $product->bg_image;
        $lp->favicon_image = $product->favicon_image;
        $lp->upsell_hero_image = $product->upsell_hero_image;
        $lp->vimeo_id = $product->vimeo_id;
        $lp->unit_qty = !empty($product->unit_qty) ? $product->unit_qty : 1;
        $lp->is_discount = $product->is_discount ?? false;
        $lp->is_hygiene = $product->is_hygiene ?? false;

        $lp->prices = $this->preparePricesForLocalizeProduct($product);

        $skus = $this->prepareSkusForLocalizeProduct($product);
        if (!$skus) {
            abort(404, 'Product not available');
        }
        $lp->skus = $skus;
        $lp->has_battery = $product->hasBattery();

        $lp->reviews = $this->prepareReviewsForLocalizeProduct($product);

        $lp->page_title = $product->page_title;
        $lp->upsell_plusone_text = $product->upsell_plusone_text;
        $lp->upsell_hero_text = $product->upsell_hero_text;
        $lp->upsells = $product->upsells;
        $lp->image = $product->image;

        //FB and GA
        $lp->fb_pixel_id = $product->fb_pixel_id;
        $lp->gads_retarget_id = $product->gads_retarget_id;
        $lp->gads_conversion_id = $product->gads_conversion_id;
        $lp->gads_conversion_label = $product->gads_conversion_label;
        $lp->goptimize_id = $product->goptimize_id ?? null;
        $lp->is_europe_only = $product->is_europe_only ?? false;
        $lp->is_choice_required = $product->is_choice_required ?? false;

        $payment_api = PaymentApi::getActivePaypal();
        if ($payment_api) {
            $lp->is_paypal_hidden = $product->is_paypal_hidden ?? false;
        } else {
            $lp->is_paypal_hidden = true;
        }

        // disable paypal if not in countries
        $countries =  \Utils::getShippingCountries(true, $product);
        $countryCode = \Utils::getLocationCountryCode();
        if (!in_array($countryCode, $countries)) {
            $lp->is_paypal_hidden = true;
        }

        $lp->countries = $product->countries ?? [];
        // returns labels
        $labels = [];
        for ($i = 1; $i <= $product->castPriceQuantity(); $i++) {
            if (!empty($product->labels[$i])) {
                $labels[$i] = !empty($product->labels[$i][app()->getLocale()]) ? $product->labels[$i][app()->getLocale()] : (!empty($product->labels[$i]['en']) ? $product->labels[$i]['en'] : '');
            }
        }
        $lp->labels = $labels;
        // enable only for virtual for now
        if ($product->type === OdinProduct::TYPE_VIRTUAL) {
            $lp->free_files = !empty($product->free_file_ids) ? $this->getFreeFiles($product->free_file_ids) : null;
        }

        return $lp;
    }

    /**
     * Prepare prices for localize product
     * @param OdinProduct $product
     * @return array
     */
    private function preparePricesForLocalizeProduct(OdinProduct $product): array
    {
        $prices = [];
        $pricesOld = $product->prices;
        $quantityPrices = $product->castPriceQuantity();

        for ($quantity = 1; $quantity <= $quantityPrices; $quantity++) {
            if (empty($pricesOld[$quantity]['value']) || $pricesOld[$quantity]['value'] <= 0) {
                logger()->error("*Price is 0 for {$product->product_name}", ['quantity' => $quantity,'product' => $product->toArray()]);
                continue;
            }
            $prices[$quantity]['is_bestseller'] = $pricesOld[$quantity]['is_bestseller'];
            $prices[$quantity]['is_popular'] = $pricesOld[$quantity]['is_popular'];
            $prices[$quantity]['discount_percent'] = $pricesOld[$quantity]['discount_percent'];
            $prices[$quantity]['value'] = $pricesOld[$quantity]['value'];
            $prices[$quantity]['value_text'] = $pricesOld[$quantity]['value_text'];
            $prices[$quantity]['unit_value_text'] = $pricesOld[$quantity]['unit_value_text'];
            $prices[$quantity]['old_value_text'] = $pricesOld[$quantity]['old_value_text'];
            $prices[$quantity]['warranty_price_text'] = $pricesOld[$quantity]['warranty_price_text'];
            $prices[$quantity]['warranty_price'] = $pricesOld[$quantity]['warranty_price'];
            $prices[$quantity]['installments3_warranty_price_text'] = $pricesOld[$quantity]['installments3_warranty_price_text'];
            $prices[$quantity]['installments6_warranty_price_text'] = $pricesOld[$quantity]['installments6_warranty_price_text'];
            $prices[$quantity]['installments3_value_text'] = $pricesOld[$quantity]['installments3_value_text'];
            $prices[$quantity]['installments3_unit_value_text'] = $pricesOld[$quantity]['installments3_unit_value_text'];
            $prices[$quantity]['installments3_old_value_text'] = $pricesOld[$quantity]['installments3_old_value_text'];
            $prices[$quantity]['installments6_value_text'] = $pricesOld[$quantity]['installments6_value_text'];
            $prices[$quantity]['installments6_unit_value_text'] = $pricesOld[$quantity]['installments6_unit_value_text'];
            $prices[$quantity]['installments6_old_value_text'] = $pricesOld[$quantity]['installments6_old_value_text'];

            $prices[$quantity]['installments3_total_amount_text'] = $pricesOld[$quantity]['installments3_total_amount_text'];
            $prices[$quantity]['installments6_total_amount_text'] = $pricesOld[$quantity]['installments6_total_amount_text'];

            $prices[$quantity]['total_amount'] = $pricesOld[$quantity]['total_amount'];
            $prices[$quantity]['total_amount_text'] = $pricesOld[$quantity]['total_amount_text'];
        }

        if ((Route::is('splashvirtual') || Route::is('splash')) && $product->type === OdinProduct::TYPE_VIRTUAL) {
            $prices['25p']['value'] = $pricesOld['25p']['value'] ?? 0;
            $prices['25p']['value_text'] = $pricesOld['25p']['value_text'] ?? '';
        }

        $prices['currency'] = $pricesOld['currency'] ?? 'USD';
        $prices['exchange_rate'] = $pricesOld['exchange_rate'] ?? 0;

        return $prices;
    }

    /**
     * Prepare skus for localize product
     * @param OdinProduct $product
     * @return array
     */
    private function prepareSkusForLocalizeProduct(OdinProduct $product): array
    {
        $skus = [];
        $skusOld = $product->skus;
        // skus, if not published skip it
        foreach ($skusOld as $key => $sku) {
            if (!$sku['is_published']) {
                continue;
            }
            $skus[] = [
                'code' => $sku['code'],
                'name' => $sku['name'],
                'brief' => $sku['brief'],
                'has_battery' => $sku['has_battery'],
                'quantity_image' => $sku['quantity_image'],
            ];
        }
        return $skus;
    }

    /**
     * Prepare reviews for localize product
     * @param OdinProduct $product
     * @return array
     */
    private function prepareReviewsForLocalizeProduct(OdinProduct $product): array
    {
        $reviews = [];
        $reviewsOld = $product->reviews;
        $c = 1;
        // reviews
        if ($reviewsOld) {
            foreach ($reviewsOld as $key => $review) {
                $reviews[] = [
                    'name' => $review['name'],
                    'text' => $review['text'],
                    'rate' => $review['rate'],
                    'image' => $review['image'],
                    'date' => date('M d, Y', strtotime("-{$c} day"))
                ];
                $c ++;
            }
        }

        if (!$reviews) {
            $reviews = $product->getDefaultReviews();
        }
        return $reviews;
    }

    /**
     * Localize upsell
     * @param OdinProduct $product
     * @param string|null $main_sku
     * @return Localize
     */
    public function localizeUpsell(OdinProduct $product, ?string $main_sku = null): Localize
    {
        $product->setLocalImages(true);
        $lp = new Localize();
        $lp->product_name = $product->product_name;
        $lp->description = $product->description;
        $lp->long_name = $product->long_name;
        $lp->billing_descriptor = $product->billing_descriptor;
        $lp->logo_image = $product->logo_image;
        $lp->type = $product->type ?? OdinProduct::TYPE_PHYSICAL;

        if ($product->type === OdinProduct::TYPE_VIRTUAL) {
            $lp->upsell_title = $product->upsell_title;
            $lp->upsell_subtitle = $product->upsell_subtitle;
            $lp->upsell_description = $product->upsell_description;
            $lp->upsell_letter = $product->upsell_letter;
            $lp->upsell_lcta_title = $product->upsell_lcta_title;
            $lp->upsell_lcta_description = $product->upsell_lcta_description;
            $lp->upsell_video_id = $product->upsell_video_id;
        } else {
            $lp->upsell_hero_image = $product->upsell_hero_image;
            $lp->upsell_plusone_text = $product->upsell_plusone_text;
        }

        if ($main_sku && collect($product['skus'])->contains('code', $main_sku)) {
            $lp->upsell_sku = $main_sku;
        } else {
            $lp->upsell_sku = $product['skus'][0]['code'];
        }

        $lp->image = !empty($product->image[0]) ? $product->image[0] : null;
        $lp->upsellPrices = $product->upsellPrices ?? null;

        return $lp;
    }

    /**
     * @param OdinProduct $product
     * @param array $images
     * @return Localize
     */
    public function localizeCheckoutUpsell(OdinProduct $product, array $images = [])
    {
        $lp = new Localize();
        $lp->product_name = $product->product_name;
        $lp->long_name = $product->long_name;
        $lp->logo_image = $images[$product['logo_image_id']] ?? null;
        $lp->upsell_sku = $product['skus'][0]['code'];
        $lp->image = (!empty($product['image_ids'][0]) && !empty($images[$product['image_ids'][0]])) ? $images[$product['image_ids'][0]] : null;
        $lp->upsellPrices = $product->upsellPrices ?? null;
        $lp->type = $product->type ?? OdinProduct::TYPE_PHYSICAL;
        return $lp->toArray();
    }

    /**
     *
     * @param mixed $copId
     * @param mixed $countryCode
     */
    public function returnPricesByData($copId, $countryCode)
    {
        $select = ['type', 'prices', 'skus', 'warranty_percent', 'price_correction_percents', 'unit_qty'];
        $product = OdinProduct::getByCopId($copId, $select);
        if (!$product) {
            abort(404);
        }

        $currency = Currency::getByCountry(strtolower($countryCode));

        $product->currency = $currency->code;
        $prices = [];
        $pricesOld = $product->prices;

        $localeString = \Utils::getCultureCode(null, $countryCode);
        $numberFormatter = new NumberFormatter($localeString, NumberFormatter::CURRENCY);
        $symbol = $numberFormatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);

        for ($quantity = 1; $quantity <= OdinProduct::PHYSICAL_QUANTITY_PRICES; $quantity++) {
            if (!empty($pricesOld[$quantity]['value'])) {
                $prices[$quantity]['value'] = $pricesOld[$quantity]['value'];
                $prices[$quantity]['2xvalue'] = $pricesOld[$quantity]['value'] * 2;
                $prices[$quantity]['value_text'] = $pricesOld[$quantity]['value_text'];
                $prices[$quantity]['2xvalue_text'] = CurrencyService::formatCurrency($numberFormatter, $pricesOld[$quantity]['value'] * 2, $currency);;
                $prices[$quantity]['amount'] = $quantity;
                $prices[$quantity]['currency'] = $currency->code;
                $prices[$quantity]['symbol'] = $symbol;
            }
        }

        foreach (static::$amountValues as $value) {
            if (!empty($pricesOld[1]['value'])) {
                $prices[$value]['amount'] = $value;
                $prices[$value]['value'] = round($pricesOld[1]['value'] * $value, 0);
                $prices[$value]['2xvalue'] = round($pricesOld[1]['value'] * $value * 2, 0);
                $prices[$value]['value_text'] = CurrencyService::formatCurrency($numberFormatter, $prices[$value]['value'], $currency, true);
                $prices[$value]['2xvalue_text'] = CurrencyService::formatCurrency($numberFormatter, $prices[$value]['value'] * 2, $currency, true);
                $prices[$value]['currency'] = $currency->code;
                $prices[$value]['symbol'] = $symbol;
            }
        }
        return $prices;
    }

    /**
     * Get domain products
     * @return type
     */
    public static function getDomainProducts(?Domain $domain = null): array
    {
        if (!$domain) {
            $domain = Domain::getByName();
        }
        $productsLocaleSorted = [];
        if (!empty($domain->sold_products)) {
            $soldProducts = $domain->sold_products ?? [];
            if ($soldProducts) {
                arsort($soldProducts);
                $productIds = array_keys($soldProducts);

                $select = ['product_name', 'description', 'long_name', 'skus', 'prices', 'image_ids', 'warranty_percent', 'price_correction_percents'];
                $products = OdinProduct::getActiveByIds($productIds, '', true, null, $select);

                // get all images
                $imagesArray = ProductService::getProductsImagesIdsForMinishop($products);

                $currency = CurrencyService::getCurrency();
                $productsLocale = [];
                if ($products) {
                    foreach ($products as $product) {
                        $product->currencyObject = $currency;
                        $product->hide_cop_id_log = true;
                        $productsLocale[] = static::getDataForMiniShop($product, $imagesArray);
                    }
                }
                // sort products by sold qty
                $productsLocaleSorted = static::sortLocaleSoldProducts($soldProducts, $productsLocale);
            }
        }

        return $productsLocaleSorted;
    }

    /**
     * Prepare data for mini shop product
     * @param OdinProduct $product
     * @param array $images
     * @return Localize
     */
    public static function getDataForMiniShop(OdinProduct $product, array $images) {

        $lp = new Localize();
        $lp->id = $product->_id;
        $lp->product_name = $product->product_name;
        $lp->description = $product->description;
        $lp->long_name = $product->long_name;
        $lp->logo_image = $images[$product->logo_image] ?? '';

        $skus = [];
        $skusOld = $product->skus;
        // skus, if not published skip it
        foreach ($skusOld as $key => $sku) {
            if (!$sku['is_published']) {
                continue;
            }
            $skus[] = [
                'code' => $sku['code'],
                'name' => $sku['name'],
                'brief' => $sku['brief'],
                'has_battery' => $sku['has_battery'],
            ];
        }
        $lp->skus = $skus;

        $prices = [];
        $pricesOld = $product->prices;
        $quantities = $product->castPriceQuantity();
        for ($quantity = 1; $quantity <= $quantities; $quantity++) {
            if (empty($pricesOld[$quantity]['value']) || $pricesOld[$quantity]['value'] <= 0) {
                logger()->error("**Price is 0 for {$product->product_name}", ['quantity' => $quantity, 'product' => $product->toArray()]);
                continue;
            }
            $prices[$quantity]['is_bestseller'] = $pricesOld[$quantity]['is_bestseller'];
            $prices[$quantity]['is_popular'] = $pricesOld[$quantity]['is_popular'];
            $prices[$quantity]['discount_percent'] = $pricesOld[$quantity]['discount_percent'];
            $prices[$quantity]['value'] = $pricesOld[$quantity]['value'];
            $prices[$quantity]['value_text'] = $pricesOld[$quantity]['value_text'];
            $prices[$quantity]['unit_value_text'] = $pricesOld[$quantity]['unit_value_text'];
            $prices[$quantity]['old_value_text'] = $pricesOld[$quantity]['old_value_text'];
            $prices[$quantity]['warranty_price_text'] = $pricesOld[$quantity]['warranty_price_text'];
            $prices[$quantity]['warranty_price'] = $pricesOld[$quantity]['warranty_price'];
        }
        $prices['currency'] = $pricesOld['currency'] ?? 'USD';
        $prices['exchange_rate'] = $pricesOld['exchange_rate'];
        $lp->prices = $prices;

        $image_ids = $product->image_ids;
        $imagesArray = [];
        if (is_array($image_ids)) {
            foreach ($image_ids as $image_id) {
                $imagesArray[] = $images[$image_id] ?? null;
                // get only 0 element
                break;
            }
        }

        $lp->image = $imagesArray;

        return $lp;
    }

    /**
     * Return sorted and paginated data from cached and searched products arrays
     * @param array $allProducts
     * @param Collection|null $products
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getSortedAndPaginatedData(array $allProducts, ?Collection $products, int $page, int $limit): array
    {
        // calculate total pages
        if ($products) {
            $totalCount = count($products);
            $totalPages = ceil($totalCount / $limit);
            $page = max($page, 1); // get 1 page when page <= 0
            $page = min($page, $totalPages); // get last page when page > $totalPages
            $offset = ($page - 1) * $limit;
            if ($offset < 0 ) {
                $offset = 0;
            }

            // sort products by value(index before flip) for saving products sorting
            $productsSortedIds = static::sortLocaleSoldProducts($allProducts, $products, true);
            // slice sorted products depends on page
            $productsSortedIds = array_slice($productsSortedIds, $offset, $limit);

            // get products depends on page
            $select = ['product_name', 'description', 'long_name', 'skus', 'prices', 'image_ids'];
            $products = OdinProduct::getActiveByIds($productsSortedIds, '', false, null, $select);

            // get all locale products with images
            $productsLocale = static::getLocaleMinishopProducts($products);

            // sort products by value(index before flip) for saving products sorting
            $productsLocaleSorted = static::sortLocaleSoldProducts($allProducts, $productsLocale);
        } else {
            $productsLocaleSorted = [];
            $totalCount = $totalPages = 0;
        }


        return [
            'products' => $productsLocaleSorted,
            'page' => $page,
            'total' => $totalCount,
            'total_pages' => $totalPages,
            'per_page' => $limit
        ];
    }

    /**
     * Get all sold domains products
     * @param $currentDomain
     * @param int $page
     * @param string $search - search string
     * @param int $limit
     * @return array
     */
    public function getAllBySoldOrTypeDomainsProducts(Domain $currentDomain, int $page = 1, $search = '', ?int $limit = 12): array
    {
        $search = mb_strlen($search) >= 2 ? $search : '';

        if ($currentDomain->product_type) {
            $type = $currentDomain->product_type ?? null;
            $allProducts = static::getCachedProductsByType($type);
        } else {
            $allProducts = static::getCachedSoldProducts();

        }

        // after shuffle we have $key => id, but we need format id => key for saving products sorting
        $allProducts = array_flip($allProducts);
        $productIds = array_keys($allProducts);

        $productCategoryId = $currentDomain->product_category_id ?? null;
        if (empty($type)) {
            $product = !empty($currentDomain->odin_product_id) ? OdinProduct::getById($currentDomain->odin_product_id, ['type']) : null;
            $type = $product->type ?? null;
        }

        $products = OdinProduct::getActiveByIds($productIds, $search, true, $productCategoryId, ['_id'], $type);

        return static::getSortedAndPaginatedData($allProducts, $products, $page, $limit);
    }


    /**
     * Returns cached products by type
     * @param string $type
     * @return array
     */
    public static function getCachedProductsByType(string $type): array
    {
        $cacheKey = 'DomainProductsData'.ucfirst($type);
        $allProducts = Cache::get($cacheKey);
        if (!$allProducts) {
            $allProducts = OdinProduct::getProductIdsByType($type);
            shuffle($allProducts);
            Cache::put($cacheKey, $allProducts, 3600);
        }
        return $allProducts;
    }

    /**
     * Returns cached sold products
     * @return array
     */
    public static function getCachedSoldProducts(): array
    {
        $allSoldProducts = Cache::get('DomainSoldProductsData');
        if (!$allSoldProducts) {
            $domains = Domain::all(['sold_products']);
            $allSoldProducts = [];
            // collect domain products
            foreach ($domains as $domain) {
                if (!empty($domain->sold_products)) {
                    $soldProducts = $domain->sold_products ?? [];
                    if ($soldProducts) {
                        $allSoldProducts = array_merge(array_keys($soldProducts), $allSoldProducts);
                        /*
                        // calculate qty by id
                        foreach ($soldProducts as $id => $qty) {
                            if (isset($allSoldProducts[$id])) {
                                $allSoldProducts[$id] += $qty;
                            } else {
                                $allSoldProducts[$id] = $qty;
                            }
                        }
                        */
                    }
                }
            }
            // sort
            //arsort($allSoldProducts);
            // get keys and shuffle
            $allSoldProducts = array_values(array_unique($allSoldProducts));
            shuffle($allSoldProducts);
            Cache::put('DomainSoldProductsData', $allSoldProducts, 3600);
        }
        return $allSoldProducts;
    }

    /**
     * Sort sort products depends on selected products
     * @param array $soldProducts
     * @param $products
     * @param bool $is_return_id - return _id instead of document collection
     * @return array
     */
    public static function sortLocaleSoldProducts(array $soldProducts = [], $products = null, $is_return_id = false): array
    {
        $productsSorted = [];
        if ($soldProducts && $products) {
            foreach ($soldProducts as $productId => $sp) {
                foreach ($products as $localeProduct) {
                    if ($localeProduct->id == $productId) {
                        $productsSorted[] = $is_return_id ? $localeProduct->_id : $localeProduct;
                        break;
                    }
                }
            }
        }
        return $productsSorted;
    }

    /**
     * Return images array for minishop
     * @param $products
     * return array $imagesArray
     */
    public static function getProductsImagesIdsForMinishop($products): array
    {
        // get all images
        $imagesArray = [];
        $imagesIdsArray = [];
        if ($products) {
            foreach ($products as $product) {
                $imagesIds = $product->getLocalMinishopImagesIds();
                $imagesIdsArray = array_merge($imagesIdsArray, $imagesIds);
            }

            if ($imagesIdsArray) {
                $images = AwsImage::getByIds($imagesIdsArray);
                foreach ($images as $image) {
                    $imagesArray[$image->id] = !empty($image['urls'][app()->getLocale()]) ? \Utils::replaceUrlForCdn($image['urls'][app()->getLocale()]) : (!empty($image['urls']['en']) ? \Utils::replaceUrlForCdn($image['urls']['en']) : '');
                }
            }
        }
        return $imagesArray;
    }

    /**
     * Retunr locale products for minishop
     * @param $products
     * @return array
     */
    public static function getLocaleMinishopProducts($products): array
    {
        $productsLocale = [];
        if ($products) {
            $imagesArray = ProductService::getProductsImagesIdsForMinishop($products);
            $currency = CurrencyService::getCurrency();
            foreach ($products as $product) {
                $product->currencyObject = $currency;
                $product->hide_cop_id_log = true;
                $productsLocale[] = static::getDataForMiniShop($product, $imagesArray);
            }
        }
        return $productsLocale;
    }

    /**
     * Returns locale file
     * @param $id
     * @return Localize|null
     */
    public static function getLocaleFreeFileByFileId($id): ?Localize
    {
        $lp = null;
        $file = File::getById($id);
        if ($file) {
            $lp = new Localize();
            $lp->name = $file->name;
            $lp->title = $file->title;
            $lp->url = $file->getUrl();
        }
        return $lp;
    }

    /**
     * Get product for download page
     * @param OdinProduct $product
     * @param string $orderNumber
     * @param mixed $upsells
     * @return Localize
     */
    public function getLocaleDownloadProduct(OdinProduct $product, string $orderNumber, $upsells = null): Localize
    {
        $lp = new Localize();
        $lp->type = $product->type;
        $lp->product_name = $product->product_name;
        $lp->description = $product->description;
        $files = $this->getVirtualFiles($product, $upsells);
        $videos = $this->getVirtualVideos($product, $upsells);
        $lp->free_files = !empty($product->free_file_ids) ? $this->getFileUrlsByIds($product->free_file_ids, $orderNumber, $files) : null;
        $lp->sale_files = !empty($product->sale_file_ids) ? $this->getFileUrlsByIds($product->sale_file_ids, $orderNumber, $files) : null;
        $lp->sale_videos = !empty($product->sale_video_ids) ? $this->getVideoUrlsByIds($product->sale_video_ids, $orderNumber, $videos) : null;
        $lp->logo_image = $product->logo_image;
        $lp->bg_image = $product->bg_image;
        $lp->image = $product->image;
        $lp->billing_descriptor = $product->billing_descriptor;
        $lp->upsells_files = $this->collectUpsellsFiles($upsells, $orderNumber, $files);
        $lp->upsells_videos = $this->collectUpsellsVideos($upsells, $orderNumber, $videos);
        $lp->collectVirtualMediaImages();
        return $lp;
    }

    /**
     * Get all virtual files including upsells
     * @param OdinProduct $product
     * @param mixed $upsells
     * @return array
     */
    public function getVirtualFiles(OdinProduct $product, $upsells = null)
    {
        $files = null;
        $file_ids = !empty($product->free_file_ids) ?  array_merge($product->free_file_ids, []) : [];
        $file_ids = !empty($product->sale_file_ids) ?  array_merge($product->sale_file_ids, $file_ids) : $file_ids;

        if ($upsells) {
            foreach ($upsells as $upsell) {
                $file_ids = !empty($upsell->sale_file_ids) ?  array_merge($upsell->sale_file_ids, $file_ids) : $file_ids;
            }
        }
        if ($file_ids) {
            $select = ['name', 'url.en', 'title.en', 'image_id'];
            $select = app()->getLocale() != 'en' ? \Utils::addLangFieldToSelect($select, app()->getLocale()) : $select;
            $files = File::getByIds(array_unique($file_ids), $select);
        }

        return $files;
    }

    /**
     * Get all virtual videos including upsells
     * @param OdinProduct $product
     * @param null $upsells
     * @return array|\Illuminate\Database\Eloquent\Collection|null
     */
    public function getVirtualVideos(OdinProduct $product, $upsells = null)
    {
        $videos = null;
        $video_ids = !empty($product->sale_video_ids) ? array_merge($product->sale_video_ids, []) : [];

        if ($upsells) {
            foreach ($upsells as $upsell) {
                $video_ids = !empty($upsell->sale_video_ids) ?  array_merge($upsell->sale_video_ids, $video_ids) : $video_ids;
            }
        }

        if (!$videos) {
            $select = ['share_id.en', 'title.en', 'image_id'];
            $select = app()->getLocale() != 'en' ? \Utils::addLangFieldToSelect($select, app()->getLocale()) : $select;
            $videos = Video::getByIds($video_ids, $select);
        }

        return $videos;
    }

    /**
     * Get file urls for download by ids
     * @param array|null $file_ids
     * @param string $orderNumber
     * @param mixed $files
     * @return array
     */
    private function getFileUrlsByIds(?array $file_ids, string $orderNumber, $files = null): array
    {
        $urls = [];
        if ($file_ids) {
            // if we haven't files get a files from ids
            if (!$files) {
                $select = ['name', 'url.en', 'title.en', 'image_id'];
                $select = app()->getLocale() != 'en' ? \Utils::addLangFieldToSelect($select, app()->getLocale()) : $select;
                $files = File::getByIds($file_ids, $select);
            }
            if ($files) {
                foreach ($file_ids as $id) {
                    foreach ($files as $file) {
                        if ((string)$id === (string)$file->_id) {
                            $urls[] = [
                                'name' => $file->name,
                                'title' => $file->title,
                                'url' => static::getDownloadFileUrl($file, $orderNumber),
                                'image_id' => (string)$file->image_id,
                            ];
                            break;
                        }
                    }
                }
            }
        }
        return $urls;
    }

    /**
     * Get video urls for download by ids
     * @param array|null $video_ids
     * @param string $orderNumber
     * @param mixed $videos
     * @return array
     */
    private function getVideoUrlsByIds(?array $video_ids, string $orderNumber, $videos = null): array
    {
        $urls = [];

        if ($video_ids) {
            if (!$videos) {
                $select = ['share_id.en', 'title.en', 'image_id'];
                $select = app()->getLocale() != 'en' ? \Utils::addLangFieldToSelect($select, app()->getLocale()) : $select;
                $videos = Video::getByIds($video_ids, $select);
            }
            if ($videos) {
                foreach ($video_ids as $id) {
                    foreach ($videos as $video) {
                        if ((string)$id === (string)$video->_id) {
                            $urls[] = [
                                'title' => $video->title,
                                'url' => $this->getDownloadVideoUrl($video, $orderNumber),
                                'image_id' => (string)$video->image_id,
                            ];
                        }
                    }
                }
            }
        }
        return $urls;
    }

    /**
     * Collect files for upsells
     * @param $upsells
     * @param string $orderNumber
     * @param null $files
     * @return array
     */
    public function collectUpsellsFiles($upsells, string $orderNumber, $files = null): array
    {
        $upsellFiles = [];
        if ($upsells) {
            foreach ($upsells as $upsell) {
                $upsellFiles = !empty($upsell['sale_file_ids']) ? array_merge($upsellFiles, $upsell['sale_file_ids']) : $upsellFiles;
            }
        }
        $upsellFiles = !empty($upsellFiles) ? $this->getFileUrlsByIds($upsellFiles, $orderNumber, $files) : [];
        return $upsellFiles;
    }

    /**
     * Collect videos and videos for upsells
     * @param $upsells
     * @param string $orderNumber
     * @param null $videos
     * @return array
     */
    public function collectUpsellsVideos($upsells, string $orderNumber, $videos = null): array
    {
        $upsellVideos = [];
        if ($upsells) {
            foreach ($upsells as $upsell) {
                $upsellVideos = !empty($upsell['sale_video_ids']) ? array_merge($upsellVideos, $upsell['sale_video_ids']) : $upsellVideos;
            }
        }
        $upsellVideos = !empty($upsellVideos) ? $this->getVideoUrlsByIds($upsellVideos, $orderNumber, $videos) : [];
        return $upsellVideos;
    }

    /**
     * Get download file URL
     * @param File|null $file
     * @param string $orderNumber
     * @return string|null
     */
    public function getDownloadFileUrl(?File $file, string $orderNumber): ?string
    {
        $url = null;
        if ($file) {
            if (!empty($file->url)) {
                $ext = pathinfo($file->url, PATHINFO_EXTENSION);
                $url = url("/my-files/{$orderNumber}/{$file->_id}/{$file->name}.{$ext}");
            }
        }
        return $url;
    }

    /**
     * Get download video URL
     * @param Video|null $video
     * @param string $orderNumber
     * @return string|null
     */
    public function getDownloadVideoUrl(?Video $video, string $orderNumber): ?string
    {
        $url = null;
        if ($video) {
            if (!empty($video->share_id)) {
                // TODO: replace last path to something??
                $url = url("/my-files/{$orderNumber}/{$video->_id}/video");
            }
        }
        return $url;
    }

    /**
     * Get s3 url by filename to download from files array
     * @param array|null $files
     * @param string $filename
     * @return array
     */
    public static function getFileByFilename(?array $files, string $filename): array
    {
        $accessedFile = [];
        foreach ($files as $file) {
            if (basename($file['url']) == $filename) {
                $accessedFile = $file;
                break;
            }
        }
        return $accessedFile;
    }

    /**
     * Get media by product arrays and file ID
     * @param OdinProduct $product
     * @param $product
     * @param string $mediaId
     * @param mixed $upsells
     * @return array
     */
    public function getMediaByProduct(OdinProduct $product, string $mediaId, $upsells = null): array
    {
        $media = [];
        $selectedFileId = $this->checkFileAccess($product, $mediaId, $upsells);

        if (!$selectedFileId) {
            $selectedFileId = $this->checkVideoAccess($product, $mediaId, $upsells);
            if ($selectedFileId) {
                $type = MediaAccess::TYPE_VIDEO;
            }
        } else {
            $type = MediaAccess::TYPE_FILE;
        }
        if ($selectedFileId) {
            $media = $this->getDownloadMediaById($selectedFileId, $type);
        }
        return $media;
    }

    /**
     * Check file access
     * @param OdinProduct $product
     * @param $mediaId
     * @param $upsells
     * @return null|string
     */
    private function checkFileAccess(OdinProduct $product, $mediaId, $upsells): ?string
    {
        $selectedFileId = null;
        // collect all possible files
        $files = !empty($product->free_file_ids) ?  $product->free_file_ids : [];
        $files = !empty($product->sale_file_ids) ? array_merge($product->sale_file_ids, $files) : $files;
        if ($upsells) {
            foreach ($upsells as $upsell) {
                $files = !empty($upsell->sale_file_ids) ? array_merge($upsell->sale_file_ids, $files) : $files;
            }
        }
        // check access
        if ($files) {
            foreach ($files as $id) {
                if ($mediaId == $id) {
                    $selectedFileId = $id;
                    break;
                }
            }
        }
        return $selectedFileId;
    }

    /**
     * Check video access
     * @param OdinProduct $product
     * @param $mediaId
     * @param $upsells
     * @return string|null
     */
    private function checkVideoAccess(OdinProduct $product, $mediaId, $upsells): ?string
    {
        $selectedFileId = null;
        // collect all possible videos
        $videos = !empty($product->sale_video_ids) ?  $product->sale_video_ids : [];
        if ($upsells) {
            foreach ($upsells as $upsell) {
                $videos = !empty($upsell->sale_video_ids) ? array_merge($upsell->sale_video_ids, $videos) : $videos;
            }
        }
        // check access
        if ($videos) {
            foreach ($videos as $id) {
                if ($mediaId == $id) {
                    $selectedFileId = $id;
                    break;
                }
            }
        }
        return $selectedFileId;
    }

    /**
     * Get download media by id
     * @param string $id
     * @param string $type
     * @return array
     */
    public function getDownloadMediaById(string $id, string $type): array
    {
        $file = [];
        switch ($type) {
            case MediaAccess::TYPE_FILE:
                $select = \Utils::addLangFieldToSelect(['url.en'], app()->getLocale());
                $model = File::getById($id, $select);
                $file = [
                    'type' => $type,
                    'id' => $id,
                    'url' => $model->url
                ];
                break;
            case MediaAccess::TYPE_VIDEO:
                $select = \Utils::addLangFieldToSelect(['share_id.en'], app()->getLocale());
                $model = Video::getById($id, $select);
                $file = [
                  'type' => $type,
                  'id' => $id,
                  'url' => $model->getVimeoVideo()
                ];
                break;
        }
        return $file;
    }

    /**
     * Get free files to display
     * @param array $file_ids
     * @return array
     */
    public function getFreeFiles(array $file_ids): array {
        $select = ['title.en', 'image_id'];
        $select = app()->getLocale() != 'en' ? \Utils::addLangFieldToSelect($select, app()->getLocale()) : $select;
        $files = File::getByIds($file_ids, $select);
        $files_array = [];
        foreach ($files as $file) {
            $files_array[] = [
                'title' => $file->title,
                'image_id' => $file->image_id
            ];
        }
        // collect images and remove empty values and duplicates
        $image_ids = array_column($files_array, 'image_id');
        $image_ids = array_filter(array_unique($image_ids));
        if ($image_ids) {
            $images = AwsImage::getByIds($image_ids);
            if ($images) {
                foreach ($files_array as $key => $file) {
                    foreach ($images as $image) {
                        if ((string)$image->_id == (string)$file['image_id']) {
                            $files_array[$key]['image'] = $image->getFieldLocalText($image->urls);
                            break;
                        }
                    }
                }
            }
        }
        return $files_array;
    }

}
