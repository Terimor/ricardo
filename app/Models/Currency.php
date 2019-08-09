<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Currency extends Model
{  
    protected $collection = 'currency';
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $hidden = ['auto_update', 'history'];
    
    public $timestamps = true;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'code', 'symbol', 'usd_rate', 'countries', 'created_at', 'updated_at'
    ];
    
    
}
