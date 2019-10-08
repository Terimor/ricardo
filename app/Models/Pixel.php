<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\AffiliateService;
use App\Services\CurrencyService;

class Pixel extends Model
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
        'ho_affiliate_id', 'name', 'type', 'product_ids', 'countries', 'devices', 'placements', 'code', 'is_direct_only'
    ];
    
    const DEVICE_PC = 'pc';
    const DEVICE_TABLET = 'tablet';
    const DEVICE_MOBILE = 'mobile';
    const DEVICE_TV = 'tv';        

}
