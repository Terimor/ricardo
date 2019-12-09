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
        'pay-by-card',
        'pay-by-card-upsells',
        'checkoutdotcom-captured-webhook',
        'checkoutdotcom-failed-webhook',
        'bluesnap-webhook',
        'ebanx-webhook',
        'paypal-webhooks',
        'minte-3ds/*',
        'paypal-create-order',
        'paypal-verify-order',
		'calculate-upsells-total',
        'log-data',
        /* test routes */
        'test-payments'
    ];
}
