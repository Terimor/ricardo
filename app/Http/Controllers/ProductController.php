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
        return view('product', [
            'product' => $productService->resolveProduct($request, true)
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
}
