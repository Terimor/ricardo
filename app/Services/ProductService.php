<?php

namespace App\Services;

use App\Models\OdinProduct;
use App\Models\Domain;
use Illuminate\Http\Request;

/**
 * Class ProductService
 * @package App\Services
 */
class ProductService
{
    /**
     * @param Request $request
     * @return OdinProduct
     */
    public function resolveProduct(Request $request, $needImages = false): OdinProduct
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
            $product = OdinProduct::orderBy('_id', 'desc')->firstOrFail();
        }
        
        // set local images
        if ($needImages) {
            $product->setLocalImages();
        }

        return $product;
        //abort(404);
    }
    
    /**
     * Get upsell product by ID
     * @param type $productId
     */
    public function getUpsellProductById(OdinProduct $product, string $productId, $maxQuantity = 5)
    {	
		// check upsell product by ID
		$productUpsells = !empty($product->upsells) ? $product->upsells : null;
		if (!$productUpsells) {
			logger()->error("Can't find a upsell products", ['request' => request()->all(), 'productId' => $productId, 'product' => $product->toArray()]);
			abort(404);
		}

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
			abort(404);
		}

		if ($fixedPrice && $fixedPrice < 4.5) {
			$fixedPrice = 4.5;
			logger()->error("UPSELL Price < 4.5", ['product' => $product->toArray()]);
		}

		$upsell->setUpsellPrices($fixedPrice, $discountPercent, $maxQuantity);

		return $upsell;
    }
	
	/**
	 * Calculate upsells total
	 * @param array $ulsells
	 * @param float $total
	 */
	public function calculateUpsellsTotal(OdinProduct $product, array $upsells, float $total) : array
	{
		// TODO: modify WHERE IN
		$upsellProducts = [];
		$totalSumCalc = 0;
		foreach ($upsells as $id => $quantity) {
			$upsellProduct = $this->getUpsellProductById($product, $id, $quantity);
			$totalSumCalc += !empty($upsellProduct->upsellPrices[$quantity]['value']) ? $upsellProduct->upsellPrices[$quantity]['value'] : 0;
		}
		
		if ($totalSumCalc != $total) {
			logger()->error("Total summs not equally", ['product' => $product->toArray(), 'total' => $total, 'totalSumCalc' => $totalSumCalc]);
			abort(404);
		}
		
		return [
			'value' => $totalSumCalc,
			'value_text' => CurrencyService::getLocalTextValue($totalSumCalc)
		];
		
	}
}
