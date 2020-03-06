<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class RequestQueue extends Model
{
    protected $collection = 'request_queue';

    protected $dates = ['created_at', 'request_at'];

    const UPDATED_AT = null;

    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'status', 'url', 'request_at'
    ];

    const STATUS_NEW = 'new';
    const TYPE_AFFILIATE_POSTBACK = 'affiliate_postback';
    const URL_AFFILIATE_POSTBACK = 'https://check.12buyme34.com/aff_lsr?transaction_id=';
    const DEFAULT_DELAY = 60;

    /**
     * Attributes with default values
     *
     * @var array $attributes
     */
    protected $attributes = [
        'type' => null,
        'status' => self::STATUS_NEW,
        'url' => null,
    ];

    /**
     * Save txidrow
     * @param type $txid
     */
    public static function saveTxid($txid)
    {
        if ($txid) {
            $rq = new RequestQueue();
            $rq->type = static::TYPE_AFFILIATE_POSTBACK;
            $rq->status = static::STATUS_NEW;
            $rq->url = static::URL_AFFILIATE_POSTBACK.$txid;
            $rq->request_at = \Utils::getMongoTimeFromTS(time() + static::DEFAULT_DELAY);
            $rq->save();
        }
    }

    /**
     *
     * @param string $url
     * @param int $delay
     */
    public static function saveNewRequestQuery(string $url, int $delay = self::DEFAULT_DELAY, $type = self::TYPE_AFFILIATE_POSTBACK)
    {
        if ($url) {
            $rq = new RequestQueue();
            $rq->type = $type;
            $rq->status = static::STATUS_NEW;
            $rq->url = $url;
            $rq->request_at = \Utils::getMongoTimeFromTS(time() + $delay);
            $rq->save();
        }
    }

}
