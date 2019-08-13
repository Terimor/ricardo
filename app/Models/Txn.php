<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Validator;

class Txn extends Model
{
    protected $collection = 'txn';
    
    protected $dates = ['created_at', 'updated_at'];
    
    public $timestamps = true;
    
    protected $attributes = [
        'hash' => '', // * string
        'value' => '', // * float
        'currency' => '', // * string
        'provider_data' => '',
        'payment_provider' => '', // enum string
        'payment_method' => '', // enum string        
        
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hash', 'value', 'currency', 'provider_data', 'payment_provider', 'payment_method', 'approved'
    ];
    
    /**
     * 
     */
    public static function boot()
    {
        parent::boot();
        
        self::creating(function($model) {
            if ($model->currency) {
                $model->currency = strtoupper($model->currency);
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
            'hash'     => 'required',
            'value'     => 'required',
            'currency'     => 'required',
        ]);
    }
}
