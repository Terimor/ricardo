<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Class ProductService
 * @package App\Services
 */
class ProductService
{
    /**
     * @param Request $request
     * @return Product
     */
    public function resolveProduct(Request $request): Product
    {
        if ($request->has('product')) {
            $product = Product::where('skus.code', $request->input('product'))->first();
            if ($product) {
                return $product;
            }
        }

        // Add domain resolve logic here

        abort(404);
    }
}
