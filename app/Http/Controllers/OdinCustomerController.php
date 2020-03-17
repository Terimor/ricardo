<?php

namespace App\Http\Controllers;

use App\Models\OdinCustomer;
use App\Services\CustomerService;
use App\Http\Requests\OdinCustomerRequest;

class OdinCustomerController extends Controller
{

    /**
     * Create new customer or update existing
     * @param  OdinCustomerRequest $request
     * @param  CustomerService $customerService
     * @return array
     */
    public function addOrUpdate(OdinCustomerRequest $request, CustomerService $customerService) {
        // wait random time to exclude requests at the same time
        usleep(rand(1000, 200000));
        $customerService->addOrUpdate($request->except(OdinCustomer::$exceptFromRequest));
        return '';
    }
}
