<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class AffiliatePostback extends Model
{
    protected $collection = 'pixel';
    
    protected $dates = ['created_at', 'updated_at'];
    
    public $timestamps = true;    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ho_affiliate_id', 'name', 'url', 'delay'
    ];

}
