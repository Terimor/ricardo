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
    public function resolveProduct(Request $request, $need_images = false): OdinProduct
    {
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
        if ($need_images) {
            $product->setLocalImages();
        }
        
        return $product;
        //abort(404);
    }
}
