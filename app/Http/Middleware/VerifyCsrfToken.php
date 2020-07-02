<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'pay-by-apm',
        'pay-by-card',
        'pay-by-apm-upsells',
        'pay-by-card-bs-3ds',
        'pay-by-card-upsells',
        'checkoutdotcom-captured-webhook',
        'checkoutdotcom-failed-webhook',
        'bluesnap-webhook',
        'appmax-webhook',
        'ebanx-webhook',
        'stripe-webhook',
        'paypal-webhooks',
        'novalnet-webhook/*',
        'minte-3ds/*',
        'minte-apm/*',
        'paypal-create-order',
        'paypal-verify-order',
        'calculate-upsells-total',
        'log-data',
        'apply-discount',
        /* test routes */
        'test-payments',
        'new-customer',
        'support-abc',
        'request-order-password',
        'get-order-info',
    ];
}
