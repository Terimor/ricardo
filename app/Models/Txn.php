<?php

namespace App\Models;

use App\Exceptions\TxnNotFoundException;
use Jenssegers\Mongodb\Eloquent\Model;
use Validator;

class Txn extends Model
{
    const STATUS_NEW = 'new';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_CAPTURED = 'captured';
    const STATUS_APPROVED = 'approved';
    const STATUS_FAILED = 'failed';
    const STATUS_CHARGEBACK = 'chargeback';

    protected $collection = 'txn';

    protected $dates = ['created_at', 'updated_at'];

    public $timestamps = true;

    protected $attributes = [
        'hash' => null, // * string
        'value' => null, // * float
        'currency' => null, // * string
        'provider_data' => null,
        'payment_provider' => null, // enum string
        'payment_method' => null, // enum string
        'payer_id' => null, // string
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hash', 'value', 'currency', 'provider_data', 'payment_provider', 'payment_method', 'payer_id'
    ];

    /**
     *
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function($model) {
            if ($model->currency) {
                $model->currency = strtoupper($model->currency);
            }
        });
    }

    /**
     * Returns Txn by ID
     * @param  string    $id
     * @param  boolean   $throwable default=true
     * @return Txn|null
     */
    public static function getById(string $id, bool $throwable = true): ?Txn
    {
        $model = self::find($id);
        if (!$model && $throwable) {
            throw new TxnNotFoundException("Txn [{$id}] not found");
        }
        return $model;
    }

    /**
     * Validator
     * @param array $data
     * @return type
     */
    public function validate(array $data = [])
    {
        if (!$data) {
            $data = $this->attributesToArray();
        }

        return Validator::make($data, [
            'hash'     => 'required',
            'value'     => 'required',
            'currency'     => 'required',
        ]);
    }
}
