<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class DataHistory extends Model
{
    protected $collection = 'data_history';

    protected $dates = ['created_at'];

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'collection', 'document_id', 'fields', 'is_array_changed', 'reason', 'user_id'
    ];

    /**
     *
     * @var type
     */
    protected $attributes = [
        'collection' => null, // * enum
        'document_id' => null, // * document ID, //document id in collection
        'fields' =>
		[
            //'name' => null, //field name
            //'old' => null, //old value
            //'new' => null, //new value
			//'is_array_changed' => false, //bool, default false, //true if changes were made in array — added or removed array object
        ],
		'reason' => null, // *enum string
		'user_id' => null, // Saga user ID, default null //if was changed manually in Saga
    ];

    const REASON_ODIN_UPDATE = 'odin_update';
    const REASON_CUSTOMER = 'customer';

    public static $historyIgnoredFields = ['updated_at'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * Validator
     * @param array $data
     * @return type
     */
    public function validate(array $data = [])
    {
        return Validator::make($data, [
            'collection'     => 'required',
            'document_id'    => 'required',
            'reason'    => 'required',
        ]);
    }

    /**
     * Save history from data
     * @param type $data
     */
    public static function saveHistoryData($data)
    {
		$new = new DataHistory($data);
		$new->save();
    }
}
