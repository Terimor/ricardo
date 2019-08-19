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
            'product' => $productService->resolveProduct($request)
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
     * 
     * @param Request $request
     */
    public function getLocalPrice(Request $request)
    {
        //get country code by GET or IP
        if ($request->has('cur')) {
            $currencyCode = $request->input('cur');
        } else {
            $countryCode = \Utils::getLocationCountryCode();
            //get fraction digits and locale string
            $localeString = \Utils::getCultureCode(null, $countryCode);
            $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);

            $currencyCode = $numberFormatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
        }

        echo '<pre>'; var_dump($currencyCode); echo '</pre>';

        echo '123'; exit;
    }
}
