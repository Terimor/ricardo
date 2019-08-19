<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class OdinOrder extends Model
{
    public $timestamps = true;

    protected $collection = 'odin_order';

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Attributes with default values
     *
     * @var array $attributes
     */
    protected $attributes = [
        'number' => null, // * U (O1908USXXXXXX, X = A-Z0-9)
        'status' => self::STATUS_NEW, // * enum string, default "new", ['new', 'paid', 'exported', 'shipped', 'delivered', 'cancelled']
        'currency' => null, // * enum string
        'exchange_rate' => null, // * float
        'total_paid' => null, // * float amount totally paid in local currency; decreases after refund
        'total_price' => null, // * float full price in local currency (with warranty)
        'total_price_usd' => null, // * float, full USD price (with warranty)
        'payment_provider' => null, // enum string
        'payment_method' => null, // enum string
        'payer_id' => null, // string, payer ID in payment provider system
        'customer_email' => null, // * string
        'customer_first_name' => null, // * string
        'customer_last_name' => null, // * string
        'customer_phone' => null, // * string
        'language' => null, // enum string
        'ip' => null, // string
        'shipping_country' => null, // * enum string
        'shipping_zip' => null, // * string
        'shipping_state' => null, // * string
        'shipping_city' => null, // * string
        'shipping_street' => null, // * string
        'shipping_street2' => null, // * string        
        'warehouse_id' => null,
        /**
         * collection
         * [
         *  "number" => string,
         *  "aftership_slug" => enum string,
         * ]
         */
        'trackings' => [],
        /**
         * collection
         * [
         *      "sku_code" => null, // string,
         *      "quantity" => null, // int,
         *      "price" => null, // float,
         *      "price_usd" => null, // float, calculated from local price using exchange rate
         *      "warranty_price" => null, // float,
         *      "warranty_price_usd" => null, // float,calculated from local price using exchange rate
         *      "is_main" => false, //bool, product is main, not upsells and not accessories
         *      'txn_hash' => null, // string
         *      'txn_value' => null, // float decreases after refund
         *      'txn_approved' => false, // bool
         *      'txn_charged_back' => false, // bool
         *      'is_exported' => false, //bool, default false,
         *      'is_plus_one' => false, //bool,default false, upsells product the same as main product
         * ]
         */
        'products' => [],
        'ipqualityscore' => null, // object
        'page_checkout' => null, // string full checkout page address with parameters
        'flagged' => false, // bool, default false
        'offer' => null, // string
        'affiliate' => null, // string
        'is_refunding' => false, // bool, default false, refund requested, processing        
        'is_refunded' => false, // bool, order was fully or partially refunded
        'qc_passed' => false, // bool, additional control of order's correctness
    ];

    const STATUS_NEW = 'new';
    const STATUS_PAID = 'paid';
    const STATUS_HALFPAID = 'halfpaid';
    const STATUS_EXPORTED = 'exported';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    public static $statuses = [
        self::STATUS_NEW => 'New',
        self::STATUS_PAID => 'Paid',
        self::STATUS_EXPORTED => 'Exported',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_CANCELLED => 'Cancelled',
        self:: STATUS_HALFPAID => 'Halfpaid',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'status', 'currency', 'exchange_rate', 'total_paid', 'total_price', 'total_price_usd', 'payment_provider', 'payment_method', 'payer_id',
        'customer_id', 'customer_email', 'customer_first_name', 'customer_last_name', 'customer_phone', 'language', 'ip', 'shipping_country',
        'shipping_zip', 'shipping_state', 'shipping_city', 'shipping_street', 'shipping_street2', 'exported', 'warehouse_id', 'trackings', 'products',
        'ipqualityscore', 'page_checkout', 'flagged', 'offer', 'affiliate', 'is_refunding', 'is_refunded', 'qc_passed'

    ];

    /**
     *
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function($model) {
            if (!isset($model->number) || !$model->number) {
                $model->number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Validator
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate(array $data = [])
    {

        if (!$data) {
            $data = $this->attributesToArray();
        }

        return Validator::make($data, [
            'number'     => 'required|unique:odin_order',
            'currency'     => 'required',
            'total_paid'     => 'required|numeric',
            'total_price' => 'required|numeric',
            'total_price_usd' => 'required|numeric',
            'exchange_rate' => 'required|numeric',
        ]);
    }

    /**
     * Generate order number
     * @param string $countryCode
     * @return string
     */
    public static function generateOrderNumber(string $countryCode = 'XX'): string
    {
        $i = 0;
        do {
            $numberString = strtoupper('O'.date('y').date('m').$countryCode.\Utils::randomString(6));

            //check unique
            $model = OdinOrder::where(['number' => $numberString])->first();
            $i++;
            if ($i > 2) {
                logger()->error("Generate order number - {$i} iteration", ['number' => $numberString]);
            }
        } while ($model);

        return $numberString;
    }

    /**
     * Returns type as a text
     * @return string
     */
    public function getStatusText()
    {
        if (!empty(static::$statuses[$this->status])) {
            return static::$statuses[$this->status];
        } else {
            return 'Unknown';
        }
    }

    /**
     * @param $value
     */
    public function setShippingCountryAttribute($value)
    {
        $this->attributes['shipping_country'] = strtolower($value);
    }

    /**
     * Setter customer email
     */
    public function setCustomerEmailAttribute($value)
    {
        $this->attributes['customer_email'] =  strtolower(trim($value));
    }
}
