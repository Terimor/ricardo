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
        'number' => null, // * U (O1908USXXXXXX, X = A-Z0-9)
        'status' => 'new', // * enum string, default "new", ['new', 'paid', 'exported', 'shipped', 'delivered', 'cancelled']
        'currency' => null, // * enum
        'total_paid' => null, // * float amount totally paid in local currency; decreases after refund
        'total_price' => null, // * float full price in local currency (with warranty)
        'total_price_usd' => null, // * float, full USD price (with warranty)
        'payment_provider' => null, // enum string
        'payment_method' => null, // enum string
        'customer_id' => null, // * OdinCustomer id
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
        'exported' => false, // bool, default false
        'warehouse_id' => null,
        'trackings' => [
            'number' => null, // string
            'aftership_slug' => null, // enum string
        ],
        'products' => [
            'sku_code' => null, // string
            'quantity' => null, // int
            'price' => null, // float
            'price_usd' => null, // float
            'warranty_price' => null, // float
            'warranty_price_usd' => null, // float
            'is_main' => null, // bool
            'txn_hash' => null, // string
            'txn_value' => null, // float decreases after refund
            'txn_approved' => false, // bool
            'txn_charged_back' => false, // bool
        ],
        'ipqualityscore' => null, // object
        'page_checkout' => null, // string        
        'flagged' => false, // bool, default false
        'offer' => null, // string
        'affiliate' => null, // string
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
        'number', 'status', 'currency', 'total_paid', 'total_price', 'total_price_usd', 'payment_provider', 'payment_method', 'customer_id', 'customer_email', 'customer_first_name',
        'customer_last_name', 'customer_phone', 'language', 'ip', 'shipping_country', 'shipping_zip', 'shipping_state', 'shipping_city', 'shipping_street',
        'exported', 'warehouse_id', 'trackings', 'products', 'ipqualityscore', 'page_checkout', 'flagged', 'offer', 'affiliate', 'is_refunding'
        
    ];

    /**
     * 
     */
    public static function boot()
    {
        parent::boot();
        
        self::creating(function($model) {
            if (!isset($model->number) || !$model->number) {
                $model->number = $this->generateOrderNumber();
            }
            
            if ($model->shipping_country) {
                $model->shipping_country = strtoupper($model->shipping_country);
            }
            
        });
    }
    
    /**
     * Validator
     * @param array $data
     * @return type
     */
    public function validate(array $data = [])
    {
        
        if (!$data) {
            $data = $this->attributesToArray();
        }
        
        return Validator::make($data, [
            'number'     => 'required',
            'currency'     => 'required',
            'total_paid'     => 'required|numeric',
            'total_price' => 'required|numeric',
            'total_price_usd' => 'required|numeric',
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
    public static function generateOrderNumber(string $countryCode = 'XX'): string
    {
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
