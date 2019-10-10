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
        'code', 'reducing_percent',
    ];
    
    const TAG_ALWAYS = 100;
    const TAG_AFFILIATE = 60;
    const TAG_AFFILIATE_PAGES = ['thankyou-promos', 'thankyou'];
    
    /**
     * Get google tags to display
     * @param Request $request
     * @param \App\Models\AffiliateSetting $affiliate
     */
    public static function getGoogleTagsForDisplay(Request $request, AffiliateSetting $affiliate = null)
    {
        $googleTags = GoogleTag::getCacheGoogleTags();
        
        $tags = [];
        // prepare tags
        foreach ($googleTags as $key => $googleTags) {
            if ($googleTags->reducing_percent == self::TAG_ALWAYS) {
                $tags[$key]['code'] = $googleTags->code; 
            } else if ($googleTags->reducing_percent == self::TAG_AFFILIATE){
                // check affiliate < 11
                if ($affiliate && (int)$affiliate->ho_affiliate_id < 11) {
                    // check order for is reduced
                    if (!empty($request->order)) {
                        $order = OdinOrder::where('_id', $request->order)->first();
                        if (isset($order->is_reduced) && $order->is_reduced == true) {
                           $tags[$key]['code'] = $googleTags->code;
                        }
                    }
                }
            }
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
        $tags = Cache::get('google_tags');
        if (!$tags) {
            $tags = GoogleTag::all();
            Cache::put('google_tags', $tags, $cache_lifetime);
        }
        return $tags;  
    }
}
