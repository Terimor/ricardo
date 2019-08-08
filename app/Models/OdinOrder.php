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
     *
     * @var type 
     */
    protected $attributes = [
        'number' => '', // * U (O1908USXXXXXX, X = A-Z0-9)
        'status' => 'new', // * enum string, default "new", ['new', 'paid', 'exported', 'shipped', 'delivered', 'cancelled']
        'currency' => '', // * enum
        'total_paid' => '', // * float
        'payment_hash' => '', // string
        'payment_provider' => '', // enum string
        'payment_method' => '', // enum string
        'customer_id' => '', // * OdinCustomer id
        'customer_email' => '', // * string
        'customer_first_name' => '', // * string
        'customer_last_name' => '', // * string
        'customer_phone' => '', // * string
        'language' => '', // enum string
        'ip' => '', // string
        'shipping_country' => '', // * enum string
        'shipping_zip' => '', // * string
        'shipping_state' => '', // * string
        'shipping_city' => '', // * string
        'shipping_street' => '', // * string        
        'exported' => false, // bool, default false
        'warehouse_id' => '',
        'trackings' => [
            'number' => '', // string
            'aftership_slug' => '', // enum string
        ],
        'products' => [
            'sku_code' => '', // string
            'quantity' => '', // int
            'price' => '', // float
            'price_usd' => '', // float
            'is_main' => '', // bool
        ],
        'ipqualityscore' => '', // object
        'page_checkout' => '', // string        
        'flagged' => false, // bool, default false
        'offer' => '', // string
        'affiliate' => '', // string
        'txns' => [
            'txn_id' => '', // Txn id
            'hash' => '', // string
            'value' => '', // float
            'approved' => '', // bool
            'refunded' => false, // bool
            'charged_back' => false, // bool
        ],
        'is_refunding' => false, // bool, default false,
    ];
    
    const STATUS_NEW  = 'new';
    const STATUS_PAID  = 'paid';
    const STATUS_EXPORTED  = 'exported';
    const STATUS_SHIPPED  = 'shipped';
    const STATUS_DELIVERED  = 'delivered';
    const STATUS_CANCELLED  = 'cancelled';

    public static $statuses = [
      self::STATUS_NEW => 'New',
      self::STATUS_PAID => 'Paid',
      self::STATUS_EXPORTED => 'Exported',
      self::STATUS_SHIPPED => 'Shipped',
      self::STATUS_DELIVERED => 'Delivered',
      self::STATUS_CANCELLED => 'Cancelled',
    ];    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'status', 'currency', 'total_paid', 'payment_hash', 'payment_provider', 'payment_method', 'customer_id', 'customer_email', 'customer_first_name',
        'customer_last_name', 'customer_phone', 'language', 'ip', 'shipping_country', 'shipping_zip', 'shipping_state', 'shipping_city', 'shipping_street',
        'exported', 'warehouse_id', 'trackings', 'products', 'ipqualityscore', 'page_checkout', 'flagged', 'offer', 'affiliate', 'txns', 'is_refunding'
        
    ];

    /**
     * Validator
     * @param array $data
     * @return type
     */
    public function validate(array $data)
    {
        return Validator::make($data, [
            'number'     => 'required|unique',
            'currency'     => 'required',
            'total_paid'     => 'required|numeric',
            'customer_id'     => 'required',
            'customer_email'     => 'required|email',
            'customer_first_name'     => 'required',
            'customer_last_name'     => 'required',
            'customer_phone'     => 'required',
            'shipping_country'     => 'required',
            'shipping_zip'     => 'required',
            'shipping_state'     => 'required',
            'shipping_city'     => 'required',
            'shipping_street'     => 'required',
            'customer_phone'     => 'required',
        ]);
    }
    
    /**
     * Generate order number
     * @param type $countryCode
     */
    public function generateOrderNumber(string $countryCode): string
    {
        $countryCode = $countryCode ? $countryCode : 'XX';
        
        $numberString = '';

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
}
