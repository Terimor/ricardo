<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Click extends Model
{
    protected $collection = 'click';

    protected $dates = ['created_at'];

    public $timestamps = false;

    const PAGE_CHECKOUT = 'checkout';
    const PAGE_SPLASH = 'splash';

    public static $pages = [
        self::PAGE_CHECKOUT => 'Checkout',
        self::PAGE_SPLASH => 'Splash'
    ];

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

            // set device, browser, user agent
            $userAgentData = \Utils::getUserAgentParseData();
            $model->user_agent = $userAgentData['user_agent'] ?? null;
            $model->device_type = $userAgentData['device_type'] ?? null;
            $model->browser = $userAgentData['browser'] ?? null;

        });
    }

    /**
     * Save by array data
     * @param array $data
     */
    public static function saveByData(array $data)
    {
        $model = new Click();
        $model->fill($data);
        $model->save();
    }

    /**
     * Get page by url path
     * @param string $path
     * @return string
     */
    public static function getPageByPath(string $path): ?string
    {
        $page = null;
        $path = explode('/', $path);
        if (!empty($path[1]) && isset(Click::$pages[strtolower($path[1])])) {
           $page = $path[1];
        }
        return $page;
    }

}
