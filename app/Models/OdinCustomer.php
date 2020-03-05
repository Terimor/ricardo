<?php

namespace App\Models;

use App\Services\UtilsService;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Constants\CountryCustomers;

class OdinCustomer extends Model
{
    public $timestamps = true;

    protected $collection = 'odin_customer';

    protected $dates = ['created_at', 'updated_at'];

    protected $guarded = ['addresses', 'ip', 'phones'];

    /**
     *
     * @var type
     */
    protected $attributes = [
        'email' => null, // * unique string,
        'type' => 'lead', // * string,
        'first_name' => null, // * string
        'last_name' => null, // * string
        'ip' => [], // array of strings
        'phones' => [], // array of strings
        'fingerprint' => [], // array of strings
		'doc_ids' => [], // array of strings //documents numbers array
        'language' => null, // enum string
        'addresses' => [
//            'country' => null, // enum string
//            'zip' => null, // string
//            'state' => null, // string
//            'city' => null, // string
//            'street' => null, // string
//            'street2' => null, // string
//	        'apt' => null, // string
        ],
        'paypal_payer_id' => null, // string
		'number' => null, // *U (UXXXXXXXUS, X = A-Z0-9, US = country),
    ];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = [
       'email', 'first_name', 'last_name', 'language', 'paypal_payer_id', 'number', 'addresses', 'doc_ids', 'phones'
   ];

    /**
     *
     */
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
}
