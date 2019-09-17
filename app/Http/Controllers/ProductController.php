<?php

namespace App\Http\Controllers;

use App\Models\OdinProduct;
use App\Services\ProductService;
use Illuminate\Http\Request;

/**
 * Class ProductController
 * @package App\Http\Controllers
 */
class ProductController extends Controller
{
    /**
     * @param Request $request
     * @param ProductService $productService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view(Request $request, ProductService $productService)
    {
        $location = request()->get('_ip') ? Location::get(request()->get('_ip')) : Location::get('45.177.39.255');
        return view('product', [
            'product' => $productService->resolveProduct($request, true),
            'location' => $location
        ]);
    }

    /**
     * Get product by ID
     *
     * @param OdinProduct $product
     * @return OdinProduct
     */
    public function getProduct($product)
    {
        $product->load([
            'category',
            'logoImage',
            'upsellHeroImage'

        ]);
        return $product;
    }

    /**
     * Get upsell product by ID
     * @param string $productId
     * @param ProductService $productService
     * @return type
     */
    public function getUpsellProduct(string $productId, ProductService $productService)
    {
		$product = $productService->resolveProduct(request());

		$upsell = $productService->getUpsellProductById($product, $productId, request()->get('quantity'));
        
		return ['upsell' => $upsell];
    }
	
	/**
	 * Calculate upsells total
	 * @param Request $request
	 * @param ProductService $productService
	 */
	public function calculateUpsellsTotal(Request $request, ProductService $productService)
	{		
		$upsells = $request->input('upsells');
		$total = $request->input('total');
		
		if ($upsells && $total) {
			$product = $productService->resolveProduct($request);
			return $productService->calculateUpsellsTotal($product, $upsells, $total);			
		} else {
			logger()->error("Bad data for calculate upsells total", ['request' => $request->all()]);
			abort(404);
		}
		
	}
	
}
