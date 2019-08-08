<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Location;
use App\Services\CurrencyService;

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
    public function checkout()
    {                
        $location = request()->get('_ip') ? Location::get(request()->get('_ip')) : Location::get('45.177.39.255');
                
        return view('checkout', compact('location'));
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
        /*$price = 99.81;
        
        $exchangedPrice = CurrencyService::getLocalPriceFromUsd($price, 'KRW', app()->getLocale());
        echo $exchangedPrice; exit; */                
        
        $odinOrder = new \App\Models\OdinOrder();
        $odinOrder->number = $odinOrder->generateOrderNumber('US');
        echo $odinOrder->number;
        //$odinOrder->save();
        
        return view('index');
    }    
    
}
