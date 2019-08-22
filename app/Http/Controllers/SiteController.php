<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Location;
use App\Services\CurrencyService;
use App\Services\ProductService;
use App\Models\Currency;

class SiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('index');
    }

    /**
     * Show the application Contact us.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function contactUs()
    {
        return view('contact_us');
    }

    /**
     * Show the application Order tracking.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function orderTracking()
    {
        return view('order_tracking');
    }

    /**
     * Show the application Checkout.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function checkout(Request $request, ProductService $productService)
    {
        $location = request()->get('_ip') ? Location::get(request()->get('_ip')) : Location::get('45.177.39.255');
        $isShowProductOffer = request()->get('tpl') === 'emc1';

        $product = $productService->resolveProduct($request, true);
        $skusImageList = [];
        foreach($product->skus as $skus) {
            $skusImageList[] =
                [
                   'imageList' => $skus['images'],
                   'name' => $skus['name'],
                   'code' => $skus['code'],
                ];
        }
        return view('checkout', compact('location', 'product', 'isShowProductOffer', 'skusImageList'));
    }

    /**
     * Show the application Checkout.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function upsells()
    {
        $location = request()->get('_ip') ? Location::get(request()->get('_ip')) : Location::get('45.177.39.255');

        return view('uppsells_funnel', compact('location'));
    }

    /**
     * Show the application Checkout.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function thankyou()
    {
        $location = request()->get('_ip') ? Location::get(request()->get('_ip')) : Location::get('45.177.39.255');

        return view('thankyou', compact('location'));
    }

    /**
     * Show the application Product.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function products()
    {
        return view('products');
    }

    /**
     *
     * @return type
     */
    public function test()
    {
        /*$start = microtime(true);
        $location = \Location::get('240d:2:d30b:5600:55ee:f486:1527:27a8');
        echo '<pre>'; var_dump($location); echo '</pre>';
        echo 'Script time: '.(microtime(true) - $start).' sec.';

        echo '123'; exit;*/

        $currency = Currency::whereCode('USD')->first();
        echo '<pre>'; var_dump($currency); echo '</pre>'; exit;

        $price = 99.81;
        $exchangedPrice = CurrencyService::getLocalPriceFromUsd($price);
        echo '<pre>'; var_dump($exchangedPrice); echo '</pre>'; exit;

        $odinOrder = new \App\Models\OdinOrder();
        $odinOrder->number = $odinOrder->generateOrderNumber('US');
        $odinOrder->customer_email = 'asdaASDSAD@ccc.a     ';
        echo '<pre>'; var_dump($odinOrder->customer_email); echo '</pre>'; exit;
        //$odinOrder->save();

        return view('index');
    }

}
