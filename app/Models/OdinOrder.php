<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\TxnNotFoundException;
use App\Models\Txn;
use App\Services\OrderService;
use App\Services\UtilsService;
use App\Constants\CountryCustomers;

class OdinOrder extends OdinModel
{
    public $timestamps = true;

    protected $collection = 'odin_order';

    protected $dates = ['created_at', 'updated_at'];

    const EVENT_AFF_POSTBACK_SENT = 'aff_postback_sent';
    const EVENT_AFF_PIXEL_SHOWN = 'aff_pixel_shown';
    const EVENT_GTM_SHOWN = 'aff_gtm_shown';

    public static $acceptedTxnStatuses = [Txn::STATUS_CAPTURED, Txn::STATUS_APPROVED, Txn::STATUS_AUTHORIZED];
    public static $acceptedTxnFlaggedStatuses = [Txn::STATUS_CAPTURED, Txn::STATUS_APPROVED];

    /**
     * Attributes with default values
     *
     * @var array $attributes
     */
    protected $attributes = [
        'number' => null, // * U (O1908USXXXXXX, X = A-Z0-9)
        'status' => self::STATUS_NEW, // * enum string, default "new", ['new', 'paid', 'exported', 'shipped', 'delivered', 'cancelled', 'error']
        'currency' => null, // * enum string
        'exchange_rate' => null, // * float
        'total_paid' => null, // * float amount totally paid in local currency; decreases after refund
        'total_paid_usd' => null, // Full USD paid amount (with warranty); calculated from local paid amount using exchange rate; recalculated after refund.
        'total_price' => null, // * float full price in local currency (with warranty)
        'total_price_usd' => null, // * float, full USD price (with warranty)
        'total_chargeback' => 0, // total chargeback amount in local currency
        'total_chargeback_usd' => 0, // total chargeback amount in USD
        'txns_fee_usd' => 0, //float, total amount of all txns' fee in USD
        'shop_currency' => null, // enum string, //currency was used to display prices
        //'payment_provider' => null, // enum string
        //'payment_method' => null, // enum string
        'installments' => 0,
        'customer_email' => null, // * string
        'customer_first_name' => null, // * string
        'customer_last_name' => null, // * string
        'customer_phone' => null, // * string
        'customer_doc_id' => null, // * string, document number like passport
        'language' => null, // enum string
        'ip' => null, // string
        'country' => null, // string
        'shipping_country' => null, // enum string
        'shipping_zip' => null, // string
        'shipping_state' => null, // string
        'shipping_city' => null, // string
        'shipping_street' => null, // string
        'shipping_street2' => null, // string
        'shipping_apt' => null, // string
        'warehouse_id' => null,
        /**
         * collection
         * [
         *  "number" => string,
         *  "aftership_slug" => enum string,
         * ]
         */
        'trackings' => [],
        /**
         * collection
         * [
         *      "sku_code" => null, // string,
         *      "quantity" => null, // int,
         *      "price" => null, // float,
         *      "price_usd" => null, // float, calculated from local price using exchange rate
         *      "warranty_price" => null, // float,
         *      "warranty_price_usd" => null, // float,calculated from local price using exchange rate
         *      "is_main" => false, //bool, product is main, not upsells and not accessories
         *      'is_exported' => false, // bool,
         *      'is_plus_one' => false, // bool, upsells product the same as main product
         *      'is_upsells' => false, // bool, product was bought as upsells
         *      'price_set' => null,  // string,
         *      'txn_hash' => null, // string, //link to Txn hash of transaction
         * ]
         */
        'products' => [],
        'txns' => [ // — array of objects,
//	        'hash' => null, // string, //link to Txn hash
//	        'value' => null, float, //decreases after refund
//	        'status' => 'new', // — enum, default "new", ["new", "authorized", "captured", "approved", "failed"] //approved should be confirmed by webhook
//	        'fee_usd' => null,// — float, //provider's txn fee in USD
//	        'payment_provider' => '', // — enum string,
//	        'payment_method' => '', // — enum string,
//	        'payer_id' => '', // — string, //payer ID in payment provider system
//	        'card_type' => '', // — enum string,
//	        'payment_api_id' => '', // —string,
        ],
        'ipqualityscore' => null, // object
        'page_checkout' => null, // string full checkout page address with parameters
        'is_flagged' => false, // bool, default false
        'offer' => null, // string
        'affiliate' => null, // string
        'txid' => null, // string
        'billing_descriptor' => null,
        'is_reduced' => null,
        'is_invoice_sent' => false, // bool, default false
        'is_survey_sent' => false, // bool defaut false
        'is_refunding' => false, // bool, default false, refund requested, processing
        'is_refunded' => false, // bool, order was fully or partially refunded
        'is_qc_passed' => false, // bool, additional control of order's correctness
        'params' => null, // object, //stores all GET parameters with content as object, for example {tpl: "emc1", cur: "BYN"}
        'events' => null, // enum array, //happened events on order ['aff_postback_sent','aff_pixel_shown']
        'pixels' => [], //array of shown pixels with compiled values
        'postbacks' => [], //array of sent postbacks with compiled values
    ];

    const STATUS_NEW = 'new';
    const STATUS_PAID = 'paid';
    const STATUS_HALFPAID = 'halfpaid';
    const STATUS_EXPORTED = 'exported';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ERROR = 'error';

    public static $statuses = [
        self::STATUS_NEW => 'New',
        self::STATUS_PAID => 'Paid',
        self::STATUS_EXPORTED => 'Exported',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_HALFPAID => 'Halfpaid',
        self::STATUS_ERROR => 'Error',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'status', 'currency', 'exchange_rate', 'total_paid', 'total_paid_usd', 'total_price', 'total_price_usd', 'shop_currency',
        'customer_id', 'customer_doc_id', 'customer_email', 'customer_first_name', 'customer_last_name', 'customer_phone', 'language', 'ip',
        'shipping_country', 'shipping_zip', 'shipping_state', 'shipping_city', 'shipping_street', 'shipping_street2', 'exported', 'warehouse_id',
        'trackings', 'products', 'ipqualityscore', 'page_checkout', 'flagged', 'offer', 'affiliate', 'txid', 'billing_descriptor', 'is_refunding', 'is_refunded', 'qc_passed',
        'installments', 'txns', 'params', 'is_invoice_sent', 'events', 'pixels', 'postbacks'

    ];

    protected static $save_history = true;

    /**
     *
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function($model) {
            // generate and check unique order number
            if (!isset($model->number) || !$model->number) {
                $model->number = !empty($model->shipping_country) ? self::generateOrderNumber($model->shipping_country) : self::generateOrderNumber();
            }
            if (!isset($model->shop_currency) || !$model->shop_currency) {
                $model->shop_currency = $model->currency;
            }
            
            // fill country by ip
            if (!isset($model->country) || !$model->country) {
                $ip = $model->ip;
                if ($ip) {
                    $location = \Location::get($ip);
                    $model->country = $location['countryCode'];
                    
                    if (!$model->country) {
                        logger()->error("Cant find country by IP {$ip} for order $model->number");
                    }
                }
            }
        });

        self::updated(function($model) {
            $changes = $model->getChanges();
            if ($changes && isset($changes['is_flagged']) && $changes['is_flagged'] === false) {
                $original = $model->getOriginal();
                if ($original['is_flagged'] === true && !empty($original['affiliate'])) {
                    OrderService::getReducedData((string)$original['_id'], $original['affiliate']);
                }
            }
        });
    }

    /**
     * Validator
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate(array $data = [])
    {

        if (!$data) {
            $data = $this->attributesToArray();
        }

        return Validator::make($data, [
            'number'            => 'required|unique:odin_order',
            'currency'          => 'required',
            'total_paid'        => 'required|numeric',
            'total_price'       => 'required|numeric',
            'total_price_usd'   => 'required|numeric',
            'exchange_rate'     => 'required|numeric',
        ]);
    }

    /**
     * Returns OdinOrder by ID
     * @param  string    $id
     * @param  boolean   $throwable default=true
     * @return OdinOrder|null
     */
    public static function getById(string $id, bool $throwable = true): ?OdinOrder
    {
        $order = OdinOrder::find($id);
        if (!$order && $throwable) {
            throw new OrderNotFoundException("Order [{$id}] not found");
        }
        return $order;
    }

    /**
     * Returns OdinOrder by Txn ID
     * @param  string    $txn_id
     * @param  boolean   $throwable default=true
     * @return OdinOrder|null
     */
    public static function getByTxnHash(string $txn_hash, bool $throwable = true): ?OdinOrder
    {
        $order = OdinOrder::where('txns.hash', $txn_hash)->first();
        if (!$order && $throwable) {
            throw new OrderNotFoundException("Order by Txn [{$txn_hash}] not found");
        }
        return $order;
    }

    /**
     * Returns OdinOrder by ID and product info
     * @param  string   $id
     * @param  array    $info [sku => string, qty => int, is_warranty_checked => bool]
     * @return OdinOrder|null
     */
    public static function findExistedOrderForPay(string $id, array $info): ?OdinOrder
    {
        $query = OdinOrder::where('_id', $id)
            ->where('status', self::STATUS_NEW)
            ->where('products.sku_code', $info['sku'])
            ->where('products.quantity', $info['qty']);

        if (!empty($info['is_warranty_checked'])) {
            $query->where('products.warranty_price', '>', 0);
        }

        return $query->first();
    }

    /**
     * Returns OdinOrder by number
     * @param  string    $number
     * @param  boolean   $throwable default=true
     * @return OdinOrder|null
     */
    public static function getByNumber(string $number, bool $throwable = true): ?OdinOrder
    {
        $order = OdinOrder::where(['number' => $number])->first();
        if (!$order && $throwable) {
            throw new OrderNotFoundException("Order [{$number}] not found");
        }
        return $order;
    }

    /**
     * Generate order number
     * @param string $countryCode
     * @return string
     */
    public static function generateOrderNumber(string $countryCode = null): string
    {
        $countryCode = $countryCode ?? strtoupper(\Utils::getLocationCountryCode());
        $i = 0;
        do {
            $numberString = strtoupper('O'.date('y').date('m').$countryCode.\Utils::randomString(6));

            //check unique
            $model = OdinOrder::where(['number' => $numberString])->first();
            $i++;
            if ($i > 2) {
                logger()->error("Generate order number - {$i} iteration", ['number' => $numberString]);
            }
        } while ($model);

        return $numberString;
    }

    /**
     * Returns type as a text
     * @return string
     */
    public function getStatusText()
    {
        if (!empty(static::$statuses[$this->status])) {
            return static::$statuses[$this->status];
        } else {
            return 'Unknown';
        }
    }

    /**
     * @param $value
     */
    public function setShippingCountryAttribute($value)
    {
        $this->attributes['shipping_country'] = strtolower($value);
    }

    /**
     * Setter customer email
     */
    public function setCustomerEmailAttribute($value)
    {
        $this->attributes['customer_email'] =  strtolower(trim($value));
    }

    /**
     * Get first order product id (is_main)
     * @return type
     */
    public function getFirstProductId()
    {
        $sku = null;
        $productId = null;
        if ($this->products) {
            $products = $this->products;
            foreach ($products as $product) {
                if (!empty($product['is_main'])) {
                    $sku = $product['sku_code'];
                }
            }
        }

        if ($sku) {
            $skus = OdinProduct::getCacheSkusProduct();
            if (!empty($skus[$sku]['product_id'])) {
                $productId = $skus[$sku]['product_id'];
            } else {
                logger()->error("Sku not found {$sku}");
            }
        } else {
            logger()->error("Order have't main product: {$this->id}");
        }
        return $productId;
    }

    /**
     * Return param value
     * @param type $param
     * @return type
     */
    public function getParam(string $param)
    {
        $params = $this->params;
        return isset($params[$param]) ? $params[$param] : null;
    }

    /**
     * Returns main product
     * @param bool $throwable default=true
     * @return array|null
     */
    public function getMainProduct(bool $throwable = true): ?array
    {
        $product = collect($this->products)->first(function($v) {
            return $v['is_main'] === true;
        });
        if (empty($product) && $throwable) {
            throw new ProductNotFoundException("Order main product not found, order [{$this->number}]");
        }
        return $product;
    }

    /**
     * Returns product by txn_hash
     * @param string $hash
     * @param bool $throwable default=true
     * @return array|null
     */
    public function getProductByTxnHash(string $hash, bool $throwable = true): ?array
    {
        $product = collect($this->products)->first(function($v) use ($hash) {
            return $v['txn_hash'] === $hash;
        });
        if (empty($product) && $throwable) {
            throw new ProductNotFoundException("Order product [{$hash}] not found, order [{$this->number}]");
        }
        return $product;
    }

    /**
     * Returns products by txn_hash
     * @param string $hash
     * @return array
     */
    public function getProductsByTxnHash(string $hash): array
    {
        return collect($this->products)->filter(function($v) use ($hash) {
            return $v['txn_hash'] === $hash;
        })->all();
    }

    /**
     * Returns txn by hash
     * @param string $hash
     * @param bool $throwable default=true
     * @return array|null
     */
    public function getTxnByHash(string $hash, bool $throwable = true): ?array
    {
        $txn = collect($this->txns)->first(function($v) use($hash) {
            return $v['hash'] === $hash;
        });
        if (empty($txn) && $throwable) {
            throw new TxnNotFoundException("Order txn [{$hash}] not found, order [{$this->number}]");
        }
        return $txn;
    }

    /**
     * Adds product
     * @param array $product
     * @param bool  $is_order_creation
     * @param void
     */
    public function addProduct(array $product, bool $is_order_creation = false): void
    {
        $this->products = collect($this->products)
            ->reject(function ($v) use ($product, $is_order_creation) {
                if ($v['sku_code'] === $product['sku_code']) {
                    if (!$is_order_creation && !empty($v['txn_hash'])) {
                        return $v['txn_hash'] === $product['txn_hash'];
                    }
                    return true;
                }
                return false;
            })
            ->merge([
                collect($product)->only([
                    'sku_code','quantity','price','price_usd','warranty_price',
                    'warranty_price_usd','is_main','is_paid','is_exported',
                    'is_plus_one','is_upsells','price_set','txn_hash'
                ])->all()
            ])->all();
    }

    /**
     * Adds txn
     * @param array $txn
     * @param void
     */
    public function addTxn(array $txn): void
    {
        $this->txns = collect($this->txns)
            ->reject(function ($v) use ($txn) {
                return $v['hash'] === $txn['hash'];
            })
            ->merge([$txn])->all();
    }


    /**
     * Get main order SKU
     * @return type
     */
    public function getMainSku()
    {
        $sku = null;
        if ($this->products) {
            $products = $this->products;
            foreach ($products as $product) {
                if (!empty($product['is_main'])) {
                    $sku = $product['sku_code'];
                }
            }
        }
        return $sku;
    }

    /**
     * Get main order SKU
     * @return type
     */
    public function getPriceSet()
    {
        $priceSet = null;
        if ($this->products) {
            $products = $this->products;
            foreach ($products as $product) {
                if (!empty($product['is_main'])) {
                    $priceSet = $product['price_set'];
                }
            }
        }
        return $priceSet;
    }

    /**
     * Check if txn has status for reduce
     * @return boolean
     */
    public function isTxnForReduce()
    {
        $txns = $this->txns;
        $isReduce = false;
        foreach ($txns as $txn) {
            if (in_array($txn['status'], static::$acceptedTxnStatuses)) {
                $isReduce = true;
                break;
            }
        }
        return $isReduce;
    }

    /**
     * Check if txn has status for flagged
     * @return boolean
     */
    public function isTxnForFlagged()
    {
        $txns = $this->txns;
        $isNotFlagged = false;
        foreach ($txns as $txn) {
            if (in_array($txn['status'], static::$acceptedTxnFlaggedStatuses)) {
                $isNotFlagged = true;
                break;
            }
        }
        return $isNotFlagged;
    }

    /**
     * Returns customers notification data from order
     *
     * @param string|null $country_code
     * @param int $limit
     * @return array
     */
    public static function getRecentlyBoughtData(string $country_code = null, int $limit = 25): array
    {
        if (!$country_code) {
            $country_code = UtilsService::getLocationCountryCode();
        }

        $recentlyBoughtNames = $recentlyBoughtCities = [];

        // Get customers from a current users country and get their cities.
        $ordersCollection = OdinOrder::getPaidCustomersByCountry($country_code, $limit);
        if ($ordersCollection) {
            foreach ($ordersCollection as $order) {
                $name = $order->getPublicCustomerName();
                if (!in_array($name, $recentlyBoughtNames)) {
                    $recentlyBoughtNames[] = $name;
                }

                $city = $order->getPublicCityName();
                if ($city && !in_array($city, $recentlyBoughtCities)) {
                    $recentlyBoughtCities[] = $city;
                }
            }
        }

        $tempNamesCount = count($recentlyBoughtNames);
        $tempCityCount = count($recentlyBoughtCities);

        // get from constants and merge
        if (count($recentlyBoughtNames) < $limit) {
            if (isset(CountryCustomers::$list[$country_code]['names']) && is_array(CountryCustomers::$list[$country_code]['names'])) {
                shuffle(CountryCustomers::$list[$country_code]['names']);

                foreach(CountryCustomers::$list[$country_code]['names'] as $value) {
                    if (!in_array($value, $recentlyBoughtNames)) {
                        $recentlyBoughtNames[] = $value;
                        $tempNamesCount++;
                        if ($tempNamesCount >= $limit) {
                            break;
                        }
                    }
                }
            }

            if (isset(CountryCustomers::$list[$country_code]['cities']) && is_array(CountryCustomers::$list[$country_code]['cities'])) {
                shuffle(CountryCustomers::$list[$country_code]['cities']);

                foreach(CountryCustomers::$list[$country_code]['cities'] as $value) {
                    if (!in_array($value, $recentlyBoughtCities)) {
                        $recentlyBoughtCities[] = $value;
                        $tempCityCount++;
                        if ($tempCityCount >= $limit) {
                            break;
                        }
                    }
                }
            }
        }

        // if we still have < than limit get it from us
        if ($tempNamesCount < $limit) {
            $ordersCollection = OdinOrder::getPaidCustomersByCountry('us', $limit - $tempNamesCount);
            if ($ordersCollection) {
                foreach ($ordersCollection as $order) {
                    $name = $order->getPublicCustomerName();
                    if (!in_array($name, $recentlyBoughtNames)) {
                        $recentlyBoughtNames[] = $name;
                    }

                    $city = $order->getPublicCityName();
                    if ($city && !in_array($city, $recentlyBoughtCities) && $tempCityCount < $limit) {
                        $recentlyBoughtCities[] = $city;
                    }
                }
            }
        }

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
    public static function getPaidCustomersByCountry(string $country_code, int $limit = 25)
    {
        return self::select('customer_first_name', 'customer_last_name', 'shipping_city', 'shipping_country')
            ->where('shipping_country', $country_code)
            ->where('status', '!=', static::STATUS_NEW)
            ->where('status', '!=', static::STATUS_CANCELLED)
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
        return mb_convert_case(mb_strtolower($this->customer_first_name), MB_CASE_TITLE) . ' ' . mb_strtoupper(mb_substr($this->customer_last_name, 0, 1)).'.';
    }

    /**
     * Get public city name for display
     * @return type
     */
    public function getPublicCityName()
    {
        return $this->shipping_city ? mb_convert_case(mb_strtolower($this->shipping_city), MB_CASE_TITLE) : null;
    }

    /**
     * Get last order txns
     */
    public static function getLastOrders($limit = 20) {
        $orders = OdinOrder::limit($limit)->orderBy('_id', 'desc')->get();
        return $orders;
    }
}
