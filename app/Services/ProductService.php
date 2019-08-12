<?php

namespace App\Services;

use App\Models\Product;
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

        // Domain resolve logic
        $domain = Domain::where('name', request()->getHost())->first();
        if ($domain) {            
            $product = Product::where('skus.code', $domain->sku_no)->first();
            if ($product) {
                return $product;
            }
        }
        
        abort(404);
    }        
}
