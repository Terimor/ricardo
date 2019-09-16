<?php

namespace App\Services;

use App\Models\OdinProduct;
use App\Models\Domain;
use Illuminate\Http\Request;
use stdClass;

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
    public function resolveProduct(Request $request, $needImages = false)
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

        $localizedProduct = $this->localizeProduct($product);
		
        return $localizedProduct;
        //abort(404);
    }
    
    /**
     * Get upsell product by ID
     * @param type $productId
     */
    public function getUpsellProductById($product, string $productId, $maxQuantity = 5)
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
		
		// check published status
		/*$skus = [];
		foreach ($upsell->skus as $key => $sku) {
			if (!empty($sku['is_published'])) {
				$skus[] = $sku;
			}
		}		
		$upsell->skus = $skus;
		*/
        
		if (!$upsell->skus) {
			logger()->error("UPSELL skus empty or not published", ['product' => $product->toArray()]);
			abort(405, 'Method Not Allowed');
		}

		$upsell->setUpsellPrices($fixedPrice, $discountPercent, $maxQuantity);

		return $upsell;
    }
	
	/**
	 * Calculate upsells total
	 * @param array $ulsells
	 * @param float $total
	 */
	public function calculateUpsellsTotal($product, array $upsells, float $total) : array
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
			'value_text' => CurrencyService::getLocalTextValue($totalSumCalc)
		];
		
	}
    
    /**
     * 
     * @param OdinProduct $product
     * @return stdClass
     */
    public function localizeProduct(OdinProduct $product)
    {                
        // prepare localized product
        $lp = new stdClass();
        $lp->product_name = $product->product_name;
        $lp->description = $product->description;
        $lp->long_name = $product->long_name;
        $lp->billing_descriptor = $product->billing_descriptor;
        $lp->logo_image = $product->logo_image;
        $lp->upsell_logo_image = $product->upsell_logo_image;

        $prices = [];
        $pricesOld = $product->prices;
        for ($quantity = 1; $quantity <= OdinProduct::QUANTITY_PRICES; $quantity++) {
            $prices[$quantity]['is_bestseller'] = $pricesOld[$quantity]['is_bestseller'];
            $prices[$quantity]['is_popular'] = $pricesOld[$quantity]['is_popular'];
            $prices[$quantity]['discount_percent'] = $pricesOld[$quantity]['discount_percent'];
            $prices[$quantity]['value_text'] = $pricesOld[$quantity]['value_text'];
            $prices[$quantity]['unit_value_text'] = $pricesOld[$quantity]['unit_value_text'];
            $prices[$quantity]['old_value_text'] = $pricesOld[$quantity]['old_value_text'];
            $prices[$quantity]['warranty_price_text'] = $pricesOld[$quantity]['warranty_price_text'];
            $prices[$quantity]['installments3_warranty_price_text'] = $pricesOld[$quantity]['installments3_warranty_price_text'];
            $prices[$quantity]['installments6_warranty_price_text'] = $pricesOld[$quantity]['installments6_warranty_price_text'];
            $prices[$quantity]['installments3_value_text'] = $pricesOld[$quantity]['installments3_value_text'];
            $prices[$quantity]['installments3_unit_value_text'] = $pricesOld[$quantity]['installments3_unit_value_text'];
            $prices[$quantity]['installments3_old_value_text'] = $pricesOld[$quantity]['installments3_old_value_text'];
            $prices[$quantity]['installments6_value_text'] = $pricesOld[$quantity]['installments6_value_text'];
            $prices[$quantity]['installments6_unit_value_text'] = $pricesOld[$quantity]['installments6_unit_value_text'];
            $prices[$quantity]['installments6_old_value_text'] = $pricesOld[$quantity]['installments6_old_value_text'];
        }
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
        
        $lp->upsell_plusone_text = $product->upsell_plusone_text;
        $lp->upsell_hero_text = $product->upsell_hero_text;
        $lp->upsells = $product->upsells;
        $lp->image = $product->image;

        return $lp;        
    }
}
