<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class GoogleTag extends Model
{
    protected $collection = 'db2saga_google_tag';
    
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'firing_percentage',
    ];
}
