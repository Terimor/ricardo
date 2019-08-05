<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Currency extends Model
{
    protected $collection = 'db2saga_currency';
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $hidden = ['auto_update', 'history'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'code', 'symbol', 'usd_rate', 'countries', 'created_at', 'updated_at'
    ];
    
    
}
