<?php

namespace App\Http\Controllers;

use App\Models\OdinCustomer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class OdinCustomerController extends Controller
{
    public function addOrUpdate(Request $request,CustomerService $customerService) {

        return $customerService->addOrUpdate($request->input());

    }
}
