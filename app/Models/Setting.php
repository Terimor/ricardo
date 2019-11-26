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
                    $returnedValues[$keyName] = $default;
                }
            }
        } else {
            $returnedValues = static::where(['key' => $key])->first();
            if ($returnedValues) {
                $returnedValues = $returnedValues->value;
            } else {
                $returnedValues = $default;
            }
        }
        return $returnedValues;
	}

    /**
     * Increments integer value
     * @param type $key
     * @param type $inc
     * @return type
     */
    public static function incValue($key, $inc = 1)
    {
        $val = intval(static::getValue($key, 0));
        $val += $inc;
        static::setValue($key, $val);
        return $val;
    }

    /**
     * Sets value
     * @param string $key
     * @param object $value
     * @return Setting
     */
    public static function setValue($key, $value)
    {
        $model = static::where(['key' => $key])->first();
        if (!$model) {
            $model = new self();
            $model->key = $key;
            $model->description = '';
            $model->auto = true;
            $model->multiline = false;
        }
        $model->value = $value;
        $model->save();
        return $model;
    }
}
