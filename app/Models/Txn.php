<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

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
        
    ];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hash', 'value', 'currency', 'provider_data'
    ];
    
    /**
     * Validator
     * @param array $data
     * @return type
     */
    public function validate(array $data)
    {
        return Validator::make($data, [
            'hash'     => 'required',
            'value'     => 'required',
            'currency'     => 'required',
        ]);
    }
}
