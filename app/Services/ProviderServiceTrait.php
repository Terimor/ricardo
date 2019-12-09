<?php

namespace App\Services;

use App\Models\PaymentApi;

trait ProviderServiceTrait {
    /**
     * Setup PaymentApi
     * @param  array $attrs ['product_id'=>string,'payment_api_id'=>string]
     * @return PaymentApi|null
     */
    public function getPaymentApi(array $attrs): ?PaymentApi
    {
        $default = collect($this->keys)->first(function($v, $k) {
            return empty($v->product_ids);
        });

        $key = null;
        if (!empty($attrs['product_id'])) {
            $key = collect($this->keys)->first(function($v, $k) use ($attrs) {
                return in_array($attrs['product_id'], $v->product_ids);
            });
        } elseif (!empty($attrs['payment_api_id'])) {
            $key = collect($this->keys)->first(function($v, $k) use ($attrs) {
                return $attrs['payment_api_id'] === (string)$v->getIdAttribute();
            });
        }

        return $key ?? $default;
    }
}
