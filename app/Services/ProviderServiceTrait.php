<?php

namespace App\Services;

use App\Models\PaymentApi;

trait ProviderServiceTrait {

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $keys = [];

    /**
     * @var PaymentApi
     */
    private $api;

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
