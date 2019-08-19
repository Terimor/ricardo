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
    public function resolveProduct(Request $request): OdinProduct
    {
        if ($request->has('product')) {
            $product = OdinProduct::where('skus.code', $request->input('product'))->first();
            if ($product) {
                return $product;
            }
        }

        // Domain resolve logic
        $domain = Domain::where('name', request()->getHost())->first();       
        if ($domain && !empty($domain->product)) {
            echo '<pre>'; var_dump($domain->product->prices); echo '</pre>';
            return $domain->product;            
        }
        
        logger()->error("Can't find a product", ['request' => $request->all(), 'domain' => request()->getHost()]);
        
        return OdinProduct::orderBy('_id', 'desc')->first();
        //abort(404);
    }        
}
