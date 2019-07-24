<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        return view('checkout');
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
}
