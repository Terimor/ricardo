<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Category
 * @package App\Models
 */
class Domain extends Model
{
    /**
     * @var string
     */
    protected $collection = 'domain';

    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'logo', 'odin_product_id', 'ga_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->hasOne(OdinProduct::class, '_id', 'odin_product_id');
    }
}
