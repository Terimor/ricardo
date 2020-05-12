<?php

namespace App\Http\Controllers;

use App\Models\OdinProduct;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\GetPricesRequest;

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
     * Get upsell product by ID
     * @param string $productId
     * @param Request $request
     * @param ProductService $productService
     * @return array
     */
    public function getUpsellProduct(string $productId, Request $request, ProductService $productService): array
    {
		$product = $productService->resolveProductForUpsell($request);
		// for virtual product get prices for 1,2,3,10,20
		$quantity = $product->type == OdinProduct::TYPE_VIRTUAL ? 3 : $request->get('quantity', OdinProduct::QUANTITY_PRICES);
		$upsell = $productService->getUpsellProductById($product, $productId, $quantity);
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

    /**
     * Get product price
     * @param Request $request
     * @param ProductService $productService
     * @return type
     * @throws AuthorizationException
     */
    public function getProductPrice(GetPricesRequest $request, ProductService $productService)
    {
        /**
        * hack hardcode secret
        */
       $secret = $request->headers->get('X-Api-Key');
       if ($secret !== 'd55ywgzu99bq8j2ovw48kknxudjucay48ibp3h3b') {
         throw new AuthorizationException('Unauthorized');
       }

       return $productService->returnPricesByData($request->get('cop_id'), $request->get('country'));
    }

}
