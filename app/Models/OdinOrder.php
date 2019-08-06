<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class OdinOrder extends Model
{
    public $timestamps = true;
    
    protected $collection = 'db2saga_odin_order';
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $attributes = [
        'status' => 'new',
        'exported' => false,
        'flagged' => false,
        'is_refunding' => false
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
     * boot
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function($model){
            if(!isset($model->txns)) {
                $model->txns = [];
            }
            
            if (!isset($model->txns['refunded'])) {                
                $model->txns = array_merge($model->txns, ['refunded' => false]);
            }
            
            if (!isset($model->txns['charged_back'])) {
                $model->txns = array_merge($model->txns, ['charged_back' => false]);
            }            
        });
    }

    /**
     * Validator
     * @param array $data
     * @return type
     */
    public function validator(array $data)
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
    * Returns type as a text
    * @return string
    */
    public function getStatusText() {        
        if (!empty(static::$statuses[$this->status])) {
          return static::$statuses[$this->status];
        } else {
          return 'Unknown';
        }
    }    
}
