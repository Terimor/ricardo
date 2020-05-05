<?php

namespace App\Services;

use App\Models\PaymentApi;

/**
 * ProviderService
 * @package App\Services
 */
class ProviderService {

    /**
     * @var PaymentApi
     */
    protected PaymentApi $api;

    /**
     * ProviderService constructor
     * @param PaymentApi $api
     */
    public function __construct(PaymentApi $api)
    {
        $this->api = $api;
    }
}
