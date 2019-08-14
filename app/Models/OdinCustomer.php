<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class OdinCustomer extends Model
{
    public $timestamps = true;
    
    protected $collection = 'odin_customer';
    
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     *
     * @var type 
     */
    protected $attributes = [
        'email' => null, // * unique string,
        'first_name' => null, // * string
        'last_name' => null, // * string
        'ip' => [], // array of strings
        'phones' => [], // array of strings
        'language' => null, // enum string
        'addresses' => [
            'country' => null, // enum string
            'zip' => null, // string
            'state' => null, // string
            'city' => null, // string
            'street' => null, // string
            'street2' => null, // string
        ],
        'paypal_payer_id' => null, // string
    ];
        
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = [
       'email', 'first_name', 'last_name', 'ip', 'phones', 'language', 'addresses', 'paypal_payer_id'
   ];
   
   
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
            'email'     => 'required|email|unique:odin_customer',
            'first_name'    => 'required',
            'last_name'    => 'required',
        ]);
    }
    
    /**
     * Setter email
     */
    public function setEmailAttribute($value) 
    {        
        $this->attributes['email'] =  strtolower(trim($value));
    }      
}
