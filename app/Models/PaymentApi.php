<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Http\Discovery\NotFoundException;
use App\Constants\PaymentProviders;

/**
 * This is the model class for collection "payment_api".
 *
 * @property string $payment_provider
 * @property string $key
 * @property string $secret
 * @property string $login
 * @property string[] $product_ids
 * @property string[] $domain_ids
 * @property string[] $product_category_ids
 * @property string $description
 * @property bool $is_active
 * @property bool $is_apm
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class PaymentApi extends Model
{
    protected $collection = 'payment_api';

    protected $dates = ['created_at', 'updated_at'];

    protected $attributes = [
        'product_ids' => [],
        'domain_ids' => [],
        'product_category_ids' => []
    ];

    /**
     * Returns PaymentApi by ID
     * @param  string    $id
     * @param  boolean   $throwable default=true
     * @return PaymentApi|null
     */
    public static function getById(string $id, bool $throwable = true): ?PaymentApi
    {
        $model = self::find($id);
        if (!$model && $throwable) {
            throw new ModelNotFoundException("PaymentApi [{$id}] not found");
        }
        return $model;
    }

    /**
     * Returns Model by provider
     * @param array $providers
     * @param bool  $is_active default=true
     * @param bool  $is_apm default=false
     * @return Collection
     */
    public static function getAllByProviders(array $providers, bool $is_active = true, bool $is_apm = false): Collection
    {
        return PaymentApi::where(['is_active' => $is_active, 'is_apm' => $is_apm])
            ->whereIn('payment_provider', $providers)
            ->get();
    }

    /**
     * Returns active PayPal account
     * @return type
     */
    public static function getActivePaypal()
    {
        $provider = self::whereIn('payment_provider', [PaymentProviders::PAYPAL, PaymentProviders::PAYPAL_HK])
            ->where(['is_active' => true])
            ->first();
        if (!$provider) {
            logger()->warning('Paypal not active');
        }
        return $provider;
    }
}
