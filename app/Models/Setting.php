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
        if (is_array($key)) {
            $returnedValues = static::whereIn('key', $key)->pluck('value', 'key');
            // check isset values
            foreach ($key as $keyName) {
                if (empty($returnedValues[$keyName])) {
                    $returnedValues[$keyName] = $defult;
                }
            }             
        } else {        
            $returnedValues = static::where(['key' => $key])->first();
            if ($model) {
                $returnedValues = $returnedValues->value;
            } else {
                $returnedValues = $default;
            }
        }
        return $returnedValues;
	}
}
