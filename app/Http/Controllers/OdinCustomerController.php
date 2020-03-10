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
        $result = $customerService->addOrUpdate($request->input());
        return $result['success'] ?? false;
    }
}
