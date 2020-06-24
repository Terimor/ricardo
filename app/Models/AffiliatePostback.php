<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class AffiliatePostback extends Model
{
    protected $collection = 'affiliate_postback';

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

    /**
     * Get postbacks depending on affilaite_id
     * @param string $affiliateId
     * @return mixed
     */
    public static function getPostbacks(string $affiliateId) {
        $postbacks = AffiliatePostback::
        where(function ($query) use ($affiliateId) {
            $query->where('ho_affiliate_id', '=', $affiliateId)
                ->orWhere('ho_affiliate_id', '=', AffiliateSetting::ALL_INCLUDE_INTERNAL_OPTION)
                ->orWhere('ho_affiliate_id', '=', AffiliateSetting::ALL_EXCLUDE_INTERNAL_OPTION);
        })
            ->get();
        return $postbacks;
    }

}
