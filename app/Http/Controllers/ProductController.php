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
    public function getProduct(OdinProduct $product) : OdinProduct
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
	return ['usell' => $upsell];
    }
}
