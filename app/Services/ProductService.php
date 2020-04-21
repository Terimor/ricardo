<?php

namespace App\Services;

use App\Models\OdinProduct;
use App\Models\Domain;
use App\Models\Currency;
use App\Models\AwsImage;
use App\Models\PaymentApi;
use App\Models\File;
use Illuminate\Http\Request;
use App\Models\Localize;
use App\Exceptions\ProductNotFoundException;
use NumberFormatter;
use Cache;

/**
 * Class ProductService
 * @package App\Services
 */
class ProductService
{
    public static $amountValues = [10, 15, 20];

    /**
     * @param Request $request
     * @param bool $needImages - if true collect all images
     * @param null $currency
     * @param bool $isPostback
     * @return OdinProduct
     */
    public function resolveProduct(Request $request, $needImages = false, $currency = null, $isPostback = false)
    {
        $product = null;
        // prepare select
        $select = ['prices', 'skus', 'product_name', 'description.en', 'long_name.en', 'home_description.en', 'home_name.en',
            'splash_description.en', 'billing_descriptor', 'logo_image_id', 'bg_image_id', 'favicon_image_id', 'upsell_hero_image_id', 'image_ids',
            'vimeo_id.en', 'reviews', 'upsell_plusone_text.en', 'upsell_hero_text.en', 'upsells', 'fb_pixel_id', 'gads_retarget_id',
            'gads_conversion_id','gads_conversion_label', 'goptimize_id', 'is_europe_only','is_choice_required','is_paypal_hidden','countries',
            'labels.1.en', 'labels.2.en', 'labels.3.en', 'labels.4.en', 'labels.5.en', 'warehouse_id', 'warranty_percent',
            'price_correction_percents', 'unit_qty', 'is_discount', 'is_hygiene'];

        if (app()->getLocale() != 'en') {
            // add .lang
            $select = \Utils::addLangFieldToSelect($select, app()->getLocale());
        }

        if ($request->has('cop_id')) {
            $product = OdinProduct::getResolveProductForLocal(['prices.price_set' => $request->input('cop_id')], $select);
        }

        if (!$product && $request->has('product')) {
            $product = OdinProduct::getResolveProductForLocal(['skus.code' => $request->input('product')], $select);
        }

        // Domain resolve logic
        $domain = Domain::getByName();
        if (!$product) {
            if ($domain && !empty($domain->odin_product_id)) {
                $product = OdinProduct::getResolveProductForLocal(['_id' => $domain->odin_product_id], $select);
            }
        }

        if (!$product) {
            logger()->error("Can't find a product", ['request' => $request->all(), 'domain' => $domain]);
            $product = OdinProduct::orderBy('_id', 'desc')->where('skus.is_published', true)->firstOrFail();
        }

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
     * Get upsell product by ID
     *
     * @param $product
     * @param string $productId
     * @param int $maxQuantity
     * @param null $currency_code
     * @return stdClass
     */
    public function getUpsellProductById($product, string $productId, $maxQuantity = 5, $currency_code = null)
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
                    'image_ids', 'prices', 'price_correction_percents', 'warranty_percent', 'upsell_plusone_text.en', 'unit_qty'];
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

		if ($currency_code) {
		    $upsell->currency = $currency_code;
        }

		$upsell->setUpsellPrices($fixedPrice, $discountPercent, $maxQuantity);

        $upsellLocalize = $this->localizeUpsell($upsell);
        return $upsellLocalize;
		//return $upsell;
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
            $select = ['logo_image_id', 'image_ids', 'prices', 'skus.code', 'skus.is_published', 'skus.name.en', 'skus.brief.en', 'price_correction_percents', 'warranty_percent'];
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
	 * @param array $ulsells
	 * @param float $total
     * @param bool $with_extra
     * @param null $currency_code
     * @return array
	 */
	public function calculateUpsellsTotal($product, array $upsells, float $total = null, $with_extra = false, $currency_code = null) : array
	{
		// TODO: modify WHERE IN
		$upsellProducts = [];
		$totalSumCalc = 0;
		$exchange_rate = null;
		foreach ($upsells as $id => $quantity) {
			$upsellProduct = $this->getUpsellProductById($product, $id, $quantity, $currency_code);
			$totalSumCalc += !empty($upsellProduct->upsellPrices[$quantity]['price']) ? $upsellProduct->upsellPrices[$quantity]['price'] : 0;
		    $currency_code = $upsellProduct->upsellPrices[$quantity]['code'];
		    $exchange_rate = $upsellProduct->upsellPrices[$quantity]['exchange_rate'];
		}

		/*if ($totalSumCalc != $total) {
			logger()->error("Total summs not equally", ['total' => $total, 'totalSumCalc' => $totalSumCalc, 'product' => $product->toArray()]);
			abort(409);
		}*/

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

        $prices = [];
        $pricesOld = $product->prices;

        for ($quantity = 1; $quantity <= OdinProduct::QUANTITY_PRICES; $quantity++) {
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
            //$prices[$quantity]['total_unit'] = $lp->unit_qty > 1 ? t('product.unit_qty.total', ['count' => $quantity * $lp->unit_qty]) : null;
        }
        $prices['currency'] = $pricesOld['currency'] ?? 'USD';
        $prices['exchange_rate'] = $pricesOld['exchange_rate'] ?? 0;
        $lp->prices = $prices;

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
        if (!$skus) {
            abort(404, 'Product not available');
        }
        $lp->skus = $skus;
        $lp->has_battery = $product->hasBattery();

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

        $lp->reviews = $reviews;

        $lp->page_title = $product->page_title;
        $lp->upsell_plusone_text = $product->upsell_plusone_text;
        $lp->upsell_hero_text = $product->upsell_hero_text;
        $lp->upsells = $product->upsells;
        $lp->image = $product->image;

        $payment_api = PaymentApi::getActivePaypal();
        //FB and GA
        $lp->fb_pixel_id = $product->fb_pixel_id;
        $lp->gads_retarget_id = $product->gads_retarget_id;
        $lp->gads_conversion_id = $product->gads_conversion_id;
        $lp->gads_conversion_label = $product->gads_conversion_label;
        $lp->goptimize_id = $product->goptimize_id ?? null;
        $lp->is_europe_only = $product->is_europe_only ?? false;
        $lp->is_choice_required = $product->is_choice_required ?? false;

        if ($payment_api) {
            $lp->is_paypal_hidden = $product->is_paypal_hidden ?? false;
        } else {
            $lp->is_paypal_hidden = true;
        }
        // disable paypal if in excluded shipping countries
        $countryCode = \Utils::getLocationCountryCode();
        if (in_array($countryCode, \Utils::$excludeShipping) || ($lp->has_battery && in_array($countryCode, \Utils::$excludeBatteryShipping))) {
            $lp->is_paypal_hidden = true;
        }

        $lp->countries = $product->countries ?? [];
        // if we haven't special product countries selected, disable paypal if not in include shipping list
        if (!$lp->countries && !in_array($countryCode, \Utils::$includeShipping)) {
            $lp->is_paypal_hidden = true;
        }
        // returns labels
        $labels = [];
        for ($i = 1; $i <= OdinProduct::QUANTITY_PRICES; $i++) {
            if (!empty($product->labels[$i])) {
                $labels[$i] = !empty($product->labels[$i][app()->getLocale()]) ? $product->labels[$i][app()->getLocale()] : (!empty($product->labels[$i]['en']) ? $product->labels[$i]['en'] : '');
            }
        }
        $lp->labels = $labels;
        // TODO: Enable EBOOK
        //$lp->free_file_id = (string)$product->free_file_id;


        return $lp;
    }

    /**
     * Localize upsell
     * @param OdinProduct $upsell
     * @return stdClass
     */
    public function localizeUpsell(OdinProduct $product)
    {
        $product->setLocalImages(true);
        $lp = new Localize();
        $lp->product_name = $product->product_name;
        $lp->description = $product->description;
        $lp->long_name = $product->long_name;
        $lp->billing_descriptor = $product->billing_descriptor;
        $lp->logo_image = $product->logo_image;
        $lp->upsell_hero_image = $product->upsell_hero_image;
        $lp->upsell_plusone_text = $product->upsell_plusone_text;

        $lp->upsell_sku = $product['skus'][0]['code'];

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
        return $lp->toArray();
    }

    /**
     *
     * @param mixed $copId
     * @param mixed $countryCode
     */
    public function returnPricesByData($copId, $countryCode)
    {
        $product = OdinProduct::getByCopId($copId);
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

        for ($quantity = 1; $quantity <= OdinProduct::QUANTITY_PRICES; $quantity++) {
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

        for ($quantity = 1; $quantity <= OdinProduct::QUANTITY_PRICES; $quantity++) {
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
     * Get all sold domains products
     * @param $currentDomain
     * @param int $page
     * @param string $search - search string
     * @param int $limit
     * @return array
     */
    public function getAllSoldDomainsProducts(Domain $currentDomain, int $page = 1, $search = '', ?int $limit = 12): array
    {
        if (mb_strlen($search) < 2) {
            $search = '';
        }

        $allSoldProducts = static::getCachedSoldProducts();

        $productIds = array_keys($allSoldProducts);
        $productCategoryId = $currentDomain->product_category_id ?? null;
        $select = ['_id'];
        $products = OdinProduct::getActiveByIds($productIds, $search, true, $productCategoryId, $select);

        // calculate total pages
        $totalCount = count($products);
        $totalPages = ceil($totalCount / $limit);
        $page = max($page, 1); // get 1 page when page <= 0
        $page = min($page, $totalPages); // get last page when page > $totalPages
        $offset = ($page - 1) * $limit;
        if ($offset < 0 ) {
            $offset = 0;
        }

        // sort products by sales
        $productsSortedIds = static::sortLocaleSoldProducts($allSoldProducts, $products, true);
        // slice sorted products depends on page
        $productsSortedIds = array_slice($productsSortedIds, $offset, $limit);

        // get products depends on page
        $select = ['product_name', 'description', 'long_name', 'skus', 'prices', 'image_ids'];
        $products = OdinProduct::getActiveByIds($productsSortedIds, '', false, null, $select);

        // get all locale products with images
        $productsLocale = static::getLocaleMinishopProducts($products);

        // sort products by sold qty on current page
        $productsLocaleSorted = static::sortLocaleSoldProducts($allSoldProducts, $productsLocale);

        return $data = [
            'products' => $productsLocaleSorted,
            'page' => $page,
            'total' => $totalCount,
            'total_pages' => $totalPages,
            'per_page' => $limit
        ];
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
                        // calculate qty by id
                        foreach ($soldProducts as $id => $qty) {
                            if (isset($allSoldProducts[$id])) {
                                $allSoldProducts[$id] += $qty;
                            } else {
                                $allSoldProducts[$id] = $qty;
                            }
                        }
                    }
                }
            }
            // sort
            arsort($allSoldProducts);
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
    public static function getLocaleFreeFileByFileId($id) {
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

}
