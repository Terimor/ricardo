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

    /**
     * Encrypts card data
     * @param  string $plaintext
     * @param  string $password
     * @return string
     */
    public static function encrypt($plaintext, $password): string
    {
        $key = hash('sha256', $password, true);
        $iv = openssl_random_pseudo_bytes(16);

        $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

        return base64_encode($iv . $hash . $ciphertext);
    }

    /**
     * Decrypts card data
     * @param  string $cipherblock
     * @param  string $password
     * @return string|null
     */
    public static function decrypt($cipherblock, $password): ?string
    {
        $iv_hash_ciphertext = base64_decode($cipherblock);
        $iv = substr($iv_hash_ciphertext, 0, 16);
        $hash = substr($iv_hash_ciphertext, 16, 32);
        $ciphertext = substr($iv_hash_ciphertext, 48);
        $key = hash('sha256', $password, true);

        if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

        return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
