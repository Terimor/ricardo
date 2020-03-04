<?php

namespace App\Http\Controllers;

use App\Models\OdinCustomer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class OdinCustomerController extends Controller
{
    /**
     * Create new Customer or Update existing
     * @param  Request  $request
     * @param  CustomerService  $customerService
     * @return array
     */
    public function addOrUpdate(Request $request,CustomerService $customerService) {

        return $customerService->addOrUpdate($request->input());

    }
}
