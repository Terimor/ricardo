<?php

namespace App\Http\Controllers;

use StdClass;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\OdinOrder;
use App\Services\CustomerService;
use App\Services\OrderService;
use App\Services\CurrencyService;

/* use com\checkout;
  use com\checkout\ApiServices; */

class OrderController extends Controller
{

    /**
     * @var CustomerService
     */
    protected $customerService;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * Create a new controller instance.
     * @param CustomerService $customerService
     * @param OrderService $orderService
     * @return void
     */
    public function __construct(CustomerService $customerService, OrderService $orderService)
    {
        $this->customerService = $customerService;
        $this->orderService = $orderService;
    }
    
    /**
     * 
     */
    public function sendPostbacks()
    {
        $orders = OdinOrder::limit(1000)->get();
        $reduced = 0; $first_reduced = 0;
        foreach($orders as $order) {
            if (!empty($order->affiliate) && $order->total_paid_usd > 0 && $order->is_reduced === null) {
                //$l = OrderService::getReducedData((string)$order->_id, $order->affiliate);
                //if ($l->is_reduced) {
                    $reduced++;
                //}
                echo $order->number.'<br>';
            }
        }
        echo "<br><br>COUNT: $reduced<br>";        
    }

    /**
     *
     * @param type $orderId
     */
    public function orderAmountTotal($orderId)
    {
        return $this->orderService->calculateOrderAmountTotal($orderId);
    }

}
