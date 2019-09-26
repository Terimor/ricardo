<?php

namespace App\Services;

use App\Models\OdinProduct;
use App\Models\Domain;
use Illuminate\Http\Request;
use App\Models\Localize;
use App\Exceptions\ProductNotFoundException;

/**
 * Class ProductService
 * @package App\Services
 */
class ProductService
{
    /**
     * Returns product by Sku
     * @param  string $sku
     * @return OdinProduct
     * @throws OdinProductNotFoundException
     */
    public function getBySku(string $sku)
    {
        $product = OdinProduct::where('skus.code', $sku)->first();
        if (!$product) {
            throw new ProductNotFoundException("Product {$sku} not found");
        }
        return $product;
    }
    /**
     * @param Request $request
     * @return OdinProduct
     */
    public function resolveProduct(Request $request, $needImages = false, $currency = null)
    {
        $product = null;
        if ($request->has('product')) {
            $product = OdinProduct::where('skus.code', $request->input('product'))->first();
        }

        // Domain resolve logic
        if (!$product) {
            $domain = Domain::where('name', request()->getHost())->first();
            if ($domain && !empty($domain->product)) {
                $product =  $domain->product;
            }
        }

        if (!$product) {
            logger()->error("Can't find a product", ['request' => $request->all(), 'domain' => request()->getHost()]);
            $product = OdinProduct::orderBy('_id', 'desc')->where('skus.is_published', true)->firstOrFail();
        }

        // set local images
        if ($needImages) {
            $product->setLocalImages();
        }

        if ($currency) {
            $product->currency = $currency;
        }

        //return $product;
        $localizedProduct = $this->localizeProduct($product);

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
				$upsell = OdinProduct::where('_id', $productId)->first();
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
	 * Calculate upsells total
	 * @param array $ulsells
	 * @param float $total
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
        //echo '<pre>'; var_dump($product->prices); echo '</pre>'; exit;
        // prepare localized product
        $lp = new Localize();
        $lp->product_name = $product->product_name;
        $lp->description = $product->description;
        $lp->long_name = $product->long_name;
        $lp->home_description = $product->home_description;
        $lp->home_name = $product->home_name;
        $lp->billing_descriptor = $product->billing_descriptor;
        $lp->logo_image = $product->logo_image;
        $lp->upsell_hero_image = $product->upsell_hero_image;

        $prices = [];
        $pricesOld = $product->prices;
        for ($quantity = 1; $quantity <= OdinProduct::QUANTITY_PRICES; $quantity++) {
            if (empty($pricesOld[$quantity]['value']) || $pricesOld[$quantity]['value'] <= 0) {
                logger()->error("Price is 0 for {$product->product_name}", ['product' => $lp->toArray()]);
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
        }
        $prices['currency'] = $pricesOld['currency'];
        $lp->prices = $prices;

        $skus = [];
        $skusOld = $product->skus;
        // skus, if not published skip it
        foreach ($skusOld as $key => $sku) {
            if (!$sku['is_published']) {
                continue;
            }

            $skus[$key]['code'] = $sku['code'];
            $skus[$key]['name'] = $sku['name'];
            $skus[$key]['brief'] = $sku['brief'];
            $skus[$key]['has_battery'] = $sku['has_battery'];
            $skus[$key]['quantity_image'] = $sku['quantity_image'];

        }
        $lp->skus = $skus;

        $lp->page_title = $product->page_title;
        $lp->upsell_plusone_text = $product->upsell_plusone_text;
        $lp->upsell_hero_text = $product->upsell_hero_text;
        $lp->upsells = $product->upsells;
        $lp->image = $product->image;

        return $lp;
    }

    /**
     * Localize upsell
     * @param OdinProduct $upsell
     * @return stdClass
     */
    public function localizeUpsell(OdinProduct $product)
    {
        // prepare localize upsell

        $product->setLocalImages(true);

        $lp = new Localize();
        $lp->product_name = $product->product_name;
        $lp->description = $product->description;
        $lp->long_name = $product->long_name;
        $lp->billing_descriptor = $product->billing_descriptor;
        $lp->logo_image = $product->logo_image;
        $lp->upsell_hero_image = $product->upsell_hero_image;

        $lp->upsell_sku = $product['skus'][0]['code'];

        $lp->image = !empty($product->image[0]) ? $product->image[0] : null;
        $lp->upsellPrices = $product->upsellPrices ?? null;

        return $lp;
    }

}
