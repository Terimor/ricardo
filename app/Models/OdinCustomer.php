<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * This is the model class for collection "odin_customer".
 *
 * @property string $email
 * @property string $number
 * @property string $type
 * @property string $first_name
 * @property string $last_name
 * @property string $fingerprints
 * @property string $language
 * @property string $paypal_payer_id
 * @property string $last_viewed_sku_code
 * @property string $last_page_checkout
 * @property array $addresses
 * @property array $doc_ids
 * @property array $ip
 * @property array $phones
 * @property string $created_at
 * @property string $updated_at
 */
class OdinCustomer extends Model
{
    public $timestamps = true;

    protected $collection = 'odin_customer';

    protected $dates = ['created_at', 'updated_at'];

    protected $guarded = ['addresses', 'ip', 'phones'];

    const TYPE_LEAD = 'lead';
    const TYPE_BUYER = 'buyer';

    public static $exceptFromRequest = ['number', 'fingerprints', 'ip', 'phones', 'doc_ids', 'addresses', 'paypal_payer_id', 'last_page_checkout', 'last_viewed_sku_code', 'recovery_way'];

    /**
     *
     * @var type
     */
    protected $attributes = [
        'email' => null, // * unique string,
        'number' => null, // *U (UXXXXXXXUS, X = A-Z0-9, US = country),
        'type' => self::TYPE_LEAD, // * string,
        'first_name' => null, // * string
        'last_name' => null, // * string
        'fingerprints' => [], // array of strings
        'ip' => [], // array of strings
        'phones' => [], // array of strings
		'doc_ids' => [], // array of strings //documents numbers array
        'language' => null, // enum string
        'addresses' => [
//            'country' => null, // enum string
//            'zip' => null, // string
//            'state' => null, // string
//            'city' => null, // string
//            'street' => null, // string
//            'street2' => null, // string
//	          'apt' => null, // string
//            'building' => null, // string
        ],
        'paypal_payer_id' => null, // string
        'last_page_checkout' => null, // string
        'last_viewed_sku_code' => null, // string
        'recovery_way' => null, // enum [email, sms]
        'is_trusted' => null, // bool True means the customer is trusted and whitelisted for payments, false means the customer is blacklisted
    ];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = [
       'email', 'number', 'type', 'first_name', 'last_name', 'language', 'paypal_payer_id',
       'last_page_checkout', 'last_viewed_sku_code', 'recovery_way', 'is_trusted'
   ];

    public static function boot()
    {
        parent::boot();

        self::creating(function($model) {
            if (empty($model->number)) {
                $model->number = self::generateCustomerNumber();
            }
        });
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
            if (!empty($data['_id'])) {
                // skip unique for email
                $data['email'] .= $data['_id'];
            }
        }

        return Validator::make($data, [
            'email'     => 'required|email|unique:odin_customer',
            'first_name'    => 'required',
            'last_name'    => 'required',
        ]);
    }

    /**
     * Setter email
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] =  strtolower(trim($value));
    }


    /**
     * Switches to buuyer and saves
     * @return void
     */
    public function switchToBuyer(): void
    {
        $this->type = self::TYPE_BUYER;
        $this->save();
    }

    /**
     * Generate customer number
     * @param string $countryCode
     * @return string
     */
    public static function generateCustomerNumber(string $countryCode = null): string
    {
        $countryCode = $countryCode ?? strtoupper(\Utils::getLocationCountryCode());
        $i = 0;
        do {
            $numberString = strtoupper('U'.\Utils::randomString(7).$countryCode);

            //check unique
            $model = self::where(['number' => $numberString])->first();
            $i++;
            if ($i > 2) {
                logger()->error("Generate customer number - {$i} iteration", ['number' => $numberString]);
            }
        } while ($model);

        return $numberString;
    }


    /**
     *
     * @param string|null $country_code
     * @param int $limit
     * @return Collection
     */
    private static function getCustomersByCountryCode(string $country_code, int $limit = 25)
    {
        return self::select('first_name', 'last_name', 'addresses.city')
            ->where('addresses.country', $country_code)
            ->orderBy('_id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get public customer name for display
     * @return type
     */
    public function getPublicCustomerName()
    {
        return mb_convert_case(mb_strtolower($this->first_name), MB_CASE_TITLE) . ' ' . mb_strtoupper(mb_substr($this->last_name, 0, 1)).'.';
    }

    /**
     * Get public city name for display
     * @return type
     */
    public function getPublicCityName()
    {
        $adresses = $this->addresses;
        $city = $adresses[0]['city'] ?? null;
        return $city ? mb_convert_case(mb_strtolower($city), MB_CASE_TITLE) : null;
    }

    /**
     * @param string $email
     */
    public static function getByEmail(string $email) {
        return static::where('email', $email)->first();
    }

    /**
     * Get by number
     * @param string $number
     * @return mixed
     */
    public static function getByNumber(string $number)
    {
        return static::where('number', $number)->first();
    }

    /**
     * Returns last phone
     * @return string|null
     */
    public function getLastPhone(): ?string
    {
        $phone = null;
        if (!empty($this->phones) && is_array($this->phones)) {
            $phone = $this->phones[count($this->phones)-1] ?? null;
        }
        return $phone;
    }

    /**
     * Returns last address
     * @return array|null
     */
    public function getLastAddress(): ?array
    {
        $address = null;
        if (!empty($this->addresses) && is_array($this->addresses)) {
            $address = $this->addresses[count($this->addresses)-1] ?? null;
        }
        return $address;
    }

    /**
     * Check customer for trusted status
     * @param string $email
     * @return bool|null
     */
    public static function isTrustedByEmail(string $email): ?bool {
        $model = static::getByEmail($email);
        return $model->is_trusted ?? null;
    }
}
