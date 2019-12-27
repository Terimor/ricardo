<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Click extends Model
{
    protected $collection = 'click';
    
    protected $dates = ['created_at'];
    
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url', 'page', 'fingerprint', 'affiliate', 'offer', 'price_set', 'country', 'ip'
    ];
    
    /**
     *
     * @var type 
     */
    protected $attributes = [
        'url' => '', // string, Full requested URL 
        'page' => '', // string,  Page type
		'fingerprint' => null, // string, Fingerprint hash
		'affiliate' => null, // string, Affiliate ID
        'offer' => null, // string Offer ID
        'price_set' => null, // string, Price set
        'country' => null, // string, Country code
        'ip' => null, // string, User IP address
    ];    
    
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }    

}
