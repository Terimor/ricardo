<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Location;
use App\Services\CurrencyService;
use App\Services\ProductService;

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
        $product = $productService->resolveProduct($request);
        return view('checkout', compact('location', 'product'));
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
