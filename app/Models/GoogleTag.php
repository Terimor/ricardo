<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\AffiliateSetting;
use App\Models\OdinOrder;
use Illuminate\Http\Request;
use Cache;

class GoogleTag extends Model
{
    protected $collection = 'google_tag';
    
    protected $dates = ['created_at', 'updated_at'];
    
    public $timestamps = true;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'type',
    ];

    // display always at all pages
    const TYPE_ALWAYS  = 'always';
    // display only on thankyou-promos, thankyou page when we have get parameter order= and this order is_reduced=true and aff_id < 11
    const TYPE_REDUCED  = 'reduced';    
    
    const REDUCED_PAGES = ['upsells', 'thankyou'];
    
    /**
     * Get google tags to display
     * @param Request $request
     * @param \App\Models\AffiliateSetting $affiliate
     */
    public static function getGoogleTagsForDisplay(Request $request, AffiliateSetting $affiliate = null)
    {
        $googleTags = GoogleTag::getCacheGoogleTags();
        
        $order = null;
        if (!empty($request->order)) {
            $order = OdinOrder::getById($request->order, false);
            $events = $order->events ?? [];
        }
        
        $tags = []; $saveOrder = false;
        // prepare tags
        foreach ($googleTags as $key => $googleTags) {
            if ($googleTags->type == self::TYPE_ALWAYS) {
                $tags[$key]['code'] = $googleTags->code; 
            } else if ($googleTags->type == self::TYPE_REDUCED){                
                // check affiliate < 11
                if ($affiliate && (int)$affiliate->ho_affiliate_id > AffiliateSetting::OWN_AFFILIATE_MAX) {                    
                    // check order for is reduced
                    $route = $request->route()->getName() ? $request->route()->getName() : 'index';                    
                    if ($order && in_array($route, self::REDUCED_PAGES)) {                        
                        if (!empty($order->is_reduced) && empty($order->is_flagged) && !in_array(OdinOrder::EVENT_GTM_SHOWN, $events)) {
                           $tags[$key]['code'] = $googleTags->code;
                           $saveOrder = true;
                        }
                    }
                }
            }
        }
        
        if ($saveOrder) {
            $events[] = OdinOrder::EVENT_GTM_SHOWN;
            $order->events = $events;
            $order->save();
        }
        
        return $tags;
    }
    
    /**
     * return cached google tags
     * @param type $cache_lifetime
     * @return type
     */
    public static function getCacheGoogleTags($cache_lifetime = 600)
    {
        $tags = Cache::get('GoogleTags');
        if (!$tags) {
            $tags = GoogleTag::all();
            Cache::put('GoogleTags', $tags, $cache_lifetime);
        }
        return $tags;  
    }
}
