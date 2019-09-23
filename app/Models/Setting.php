<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Setting extends Model
{
    protected $collection = 'setting';
    
    protected $dates = ['updated_at'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value', 'description', 'auto'
    ];
	
	/**
	* Returns value
	* @param string $key
	* @param object $default
	* @return object
	*/
	public static function getValue($key, $default = null)
    {
        $model = null;
        
        if (is_array($key)) {            
            $model = static::whereIn('key', $key)->pluck('value', 'key');           
        } else {        
            $model = static::where(['key' => $key])->first();
        }
		if ($model) {
            if (is_array($key)) {
                return $model;
            } else {
                return $model->value;
            }
		} else {
			return $default;
		}
	}
}
