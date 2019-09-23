<?php

namespace App\Models;

use App\Services\UtilsService;
use Illuminate\Database\Eloquent\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class OdinCustomer extends Model
{
    public $timestamps = true;

    protected $collection = 'odin_customer';

    protected $dates = ['created_at', 'updated_at'];

    protected $guarded = ['addresses', 'ip', 'phones'];

    const RECENTLY_BOUGHT_LIMIT = 25;

    /**
     *
     * @var type
     */
    protected $attributes = [
        'email' => null, // * unique string,
        'first_name' => null, // * string
        'last_name' => null, // * string
        'ip' => [], // array of strings
        'phones' => [], // array of strings
		'doc_ids' => [], // array of strings //documents numbers array
        'language' => null, // enum string
        'addresses' => [
            //'country' => null, // enum string
            //'zip' => null, // string
            //'state' => null, // string
            //'city' => null, // string
            //'street' => null, // string
            //'street2' => null, // string
	    //'apt' => null, // string
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
       'email', 'first_name', 'last_name', 'language', 'paypal_payer_id', 'number', 'addresses'
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
     * Returns customers notification data
     *
     * @param string|null $country_code
     * @param int $limit
     * @return array
     */
    public static function getRecentlyBoughtData(string $country_code = null, int $limit = self::RECENTLY_BOUGHT_LIMIT): array
    {
        if (!$country_code) {
            $country_code = UtilsService::getLocationCountryCode();
        }

        $recentlyBoughtNames = $recentlyBoughtCities = [];

        // Get customers from a current users country and get their cities
        $customersCollection = self::getCustomersByCountryCode($country_code, $limit)
            ->each(function($item, $key) use (&$recentlyBoughtCities) {
                if (!empty($item['addresses']['0']['city'])) {
                    $city = $item['addresses']['0']['city'];
                    if (!in_array($city, $recentlyBoughtCities)) {
                        $recentlyBoughtCities[] = $city;
                    }
                }
            });

        // If there are not enough customers from a current users country - add customers акщь USA
        if (count($recentlyBoughtNames) < $limit && $country_code !== 'us') {
            $temp_limit = $limit - count($recentlyBoughtNames);
            // Merge Customers from a current users country with a customers from a 'us' (USA)
            $customersCollection = $customersCollection->merge(self::getCustomersByCountryCode('us', $temp_limit));
        }

        // Iterate over all customers and get their full names
        $customersCollection->each(function($item, $key) use (&$recentlyBoughtNames) {
            $temp_full_name = $item['first_name'] . ' ' . $item['last_name'];
            if (!in_array($temp_full_name, $recentlyBoughtNames)) {
                $recentlyBoughtNames[] = $temp_full_name;
            }
        });

        $recently_bought_data = [
            'recentlyBoughtNames' => $recentlyBoughtNames,
            'recentlyBoughtCities' => $recentlyBoughtCities
        ];

        return $recently_bought_data;
    }

    /**
     *
     * @param string|null $country_code
     * @param int $limit
     * @return Collection
     */
    private static function getCustomersByCountryCode(string $country_code = null, int $limit = self::RECENTLY_BOUGHT_LIMIT)
    {
        return self::select('first_name', 'last_name', 'addresses.city')
            ->where('addresses.country', $country_code)
            ->orderBy('_id', 'desc')
            ->limit($limit)
            ->get();
    }
}
