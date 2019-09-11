<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use StdClass;
use App\Models\Setting;
use App\Models\OdinOrder;


/*use com\checkout;
use com\checkout\ApiServices;*/

class OrderController extends Controller
{

    protected $orderService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }	
}
