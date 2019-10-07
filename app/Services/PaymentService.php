<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use App\Exceptions\CustomerUpdateException;
use App\Exceptions\InvalidParamsException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\OrderUpdateException;
use App\Exceptions\PaymentException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\TxnNotFoundException;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Services\CurrencyService;
use App\Services\CustomerService;
use App\Services\CheckoutDotComService;
use App\Services\OrderService;
use Http\Client\Exception\HttpException;

/**
 * Payment Service class
 */
class PaymentService
{

    const PROVIDER_PAYPAL      = 'paypal';
    const PROVIDER_EBANX       = 'ebanx';
    const PROVIDER_CHECKOUTCOM = 'checkoutcom';
    const PROVIDER_BLUESNAP    = 'bluesnap';
    const PROVIDER_NOVALNET    = 'novalnet';
    const METHOD_INSTANT_TRANSFER = 'instant_transfer';
    const METHOD_CREDITCARD       = 'creditcard';
    const METHOD_MASTERCARD       = 'mastercard';
    const METHOD_VISA             = 'visa';
    const METHOD_AMEX             = 'amex';
    const METHOD_DISCOVER         = 'discover';
    const METHOD_DINERSCLUB       = 'dinersclub';
    const METHOD_JCB              = 'jcb';
    const METHOD_HIPERCARD        = 'hipercard';
    const METHOD_AURA             = 'aura';
    const METHOD_ELO              = 'elo';
    const METHOD_PREZELEWY24      = 'prezelewy24';
    const METHOD_IDEAL            = 'ideal';
    const METHOD_EPS              = 'eps';

    const FRAUD_CHANCE_LIMIT = 90;
    const FRAUD_CHANCE_MAX = 100;

    const SUCCESS_PATH  =   '/checkout';
    const FAILURE_PATH  =   '/checkout';

    const STATUS_OK     = 'ok';
    const STATUS_FAIL   = 'fail';

    /**
     * Saga PaymentUtils::$providers
     * Payment providers
     * +3ds — 3DS is required
     * -3ds — 3DS is optional
     * excl — excluded countries
     * @var type
     */
    public static $providers = [
        self::PROVIDER_PAYPAL      => [
            'name'      => 'PayPal',
            'is_active' => true,
            'methods'   => [
                self::METHOD_INSTANT_TRANSFER => [
                    '-3ds' => ['*']
                ]
            ]
        ],
        self::PROVIDER_CHECKOUTCOM => [
            'name'      => 'Checkout.com',
            'is_active' => true,
            'methods'   => [
                self::METHOD_CREDITCARD => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_VISA       => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_MASTERCARD => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_AMEX       => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_DISCOVER   => [
                    '+3ds' => ['europe', 'by', 'in', 'il', 'sa', 'ru'],
                    '-3ds' => ['us']
                ],
                self::METHOD_DINERSCLUB => [
                    '+3ds' => ['europe', 'by', 'in', 'il', 'sa', 'ru'],
                    '-3ds' => ['us', 'ko']
                ],
                self::METHOD_JCB        => [
                    '+3ds' => ['europe', 'il', 'ko', 'id'],
                    '-3ds' => ['sg', 'jp', 'tw', 'hk', 'mo', 'th', 'vn', 'kh', 'my', 'mm']
                ],
            ]
        ],
        self::PROVIDER_EBANX       => [
            'name'      => 'EBANX',
            'is_active' => true,
            'methods'   => [
                self::METHOD_CREDITCARD => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                self::METHOD_MASTERCARD => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                self::METHOD_VISA       => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                self::METHOD_AMEX       => [
                    '-3ds' => ['br', 'mx', 'co']
                ],
                self::METHOD_DISCOVER   => [
                    '-3ds' => ['br']
                ],
                self::METHOD_DINERSCLUB => [
                    '-3ds' => ['br', 'co']
                ],
                self::METHOD_HIPERCARD  => [
                    '-3ds' => ['br']
                ],
                self::METHOD_AURA       => [
                    '-3ds' => ['br']
                ],
                self::METHOD_ELO        => [
                    '-3ds' => ['br']
                ]
            ]
        ],
        self::PROVIDER_NOVALNET    => [
            'name'      => 'Novalnet',
            'is_active' => true,
            'methods'   => [
                self::METHOD_PREZELEWY24 => [
                    '-3ds' => ['pl']
                ],
                self::METHOD_IDEAL       => [
                    '-3ds' => ['nl']
                ],
                self::METHOD_EPS         => [
                    '-3ds' => ['at']
                ],
            ]
        ]
    ];

    /**
     * Saga PaymentUtils::$methods
     * Payment methods
     * @var type
     */
    public static $methods = [
        self::METHOD_INSTANT_TRANSFER => [
            'name'      => 'PayPal',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/paypal-curved-128px.png',
            'is_active' => true
        ],
        self::METHOD_MASTERCARD       => [
            'name'      => 'MasterCard',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/mastercard-curved-128px.png',
            'is_active' => true,
        ],
        self::METHOD_CREDITCARD       => [
            'name'      => 'Credit card',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/othercard.png',
            'is_active' => true,
        ],
        self::METHOD_VISA             => [
            'name'      => 'VISA',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/visa-curved-128px.png',
            'is_active' => true,
        ],
        self::METHOD_AMEX             => [
            'name'      => 'AmEx',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/american-express-curved-128px.png',
            'is_active' => true,
        ],
        self::METHOD_IDEAL            => [
            'name'      => 'iDeal',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/ideal-curved.png',
            'is_active' => true,
        ],
        self::METHOD_EPS              => [
            'name'      => 'EPS',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/eps-curved.png',
            'is_active' => true,
        ],
        self::METHOD_PREZELEWY24      => [
            'name'      => 'Prezelewy 24',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/prezelewy24-curved.png',
            'is_active' => true,
        ],
        self::METHOD_JCB              => [
            'name'      => 'JCB',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/jcb-curved.png',
            'is_active' => true,
        ],
        self::METHOD_AURA             => [
            'name'      => 'Aura',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/aura-curved.png',
            'is_active' => true,
        ],
        self::METHOD_ELO              => [
            'name'      => 'Elo',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/elo-curved.png',
            'is_active' => true,
        ],
        self::METHOD_HIPERCARD        => [
            'name'      => 'Hipercard',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/hipercard-curved.png',
            'is_active' => true,
        ],
        self::METHOD_DISCOVER         => [
            'name'      => 'Discover',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/discover-curved-128px.png',
            'is_active' => true,
        ],
        self::METHOD_DINERSCLUB       => [
            'name'      => 'Diners Club',
            'logo'      => 'https://static-backend.saratrkr.com/image_assets/diners-curved.png',
            'is_active' => true,
        ],
    ];


    public static $installments = [
        'mx' => [

        ]
    ];

    /**
     * @var CustomerService $customerService
     */
    protected $customerService;

    /**
     * @var OrderService $orderService
     */
    protected $orderService;

    /**
     * @var ProductService $productService
     */
    protected $productService;

    /**
     * PaymentService constructor
     * @param CustomerService $customerService
     * @param OrderService $orderService
     * @param ProductService $productService
     */
    public function __construct(CustomerService $customerService, OrderService $orderService, ProductService $productService)
    {
        $this->customerService = $customerService;
        $this->orderService = $orderService;
        $this->productService = $productService;
    }

    /**
     * Creates a new order
     * @param PaymentCardCreateOrderRequest $req
     * @return array
     */
    public function createOrder(PaymentCardCreateOrderRequest $req)
    {
        ['sku' => $sku, 'qty' => $qty] = $req->get('product');
        $is_warranty = (bool)$req->input('product.is_warranty_checked', false);
        $ipqs = $req->input('ipqs', null);
        $card = $req->get('card');
        $contact = array_merge($req->get('contact'), $req->get('address'));

        // get product and localize it
        $product = OdinProduct::getBySku($sku);
        $localizedProduct = $this->productService->localizeProduct($product); // throwable

        // update customer if it has been changed
        $reply = $this->customerService->addOrUpdate(
            array_merge($contact, ['phone' => $contact['phone']['country_code'] . $contact['phone']['number']])
        );
        if (isset($reply['errors'])) {
            throw new CustomerUpdateException(json_encode($reply['errors']));
        }

        // add order
        $currency = CurrencyService::getCurrency($localizedProduct->prices['currency']);

        $price = empty($localizedProduct->prices[$qty]) ? null : $localizedProduct->prices[$qty];
        if (!$price) {
            throw new InvalidParamsException('Invalid parameter "qty"');
        }

        $order_product = [
            'sku_code'              => $sku,
            'quantity'              => (int)$qty,
            'price'                 => $price['value'],
            'price_usd'             => floor($price['value'] / $currency->usd_rate * 100) / 100,
            'is_main'               => true,
            'price_set'             => $product->prices['price_set'],
            'txn_hash'              => null,
            'warranty_price'        => 0,
            'warranty_price_usd'    => 0
        ];

        if ($is_warranty) {
            $order_product['warranty_price'] = $price['warranty_price'];
            $order_product['warranty_price_usd'] = CurrencyService::calculateWarrantyPrice(
                $product->warranty_percent,
                $order_product['price_usd']
            );
        }

        $reply = $this->orderService->addOdinOrder([
            'status'                => OdinOrder::STATUS_NEW,
            'currency'              => $currency->code,
            'exchange_rate'         => $currency->usd_rate,
            'total_paid'            => 0,
            'total_price'           => $order_product['price'] + $order_product['warranty_price'],
            'total_price_usd'       => $order_product['price_usd'] + $order_product['warranty_price_usd'],
            'txns_fee_usd'          => 0,
            'installments'          => 0,
            'customer_email'        => $contact['email'],
            'customer_first_name'   => $contact['first_name'],
            'customer_last_name'    => $contact['last_name'],
            'customer_phone'        => $contact['phone']['country_code'] . $contact['phone']['number'],
            'language'              => app()->getLocale(),
            'ip'                    => $req->ip(),
            'txns'                  => [],
            'shipping_country'      => $contact['country'],
            'shipping_zip'          => $contact['zip'],
            'shipping_state'        => $contact['state'],
            'shipping_city'         => $contact['city'],
            'shipping_street'       => $contact['street'],
            'shop_currency'         => $currency->code,
            'warehouse_id'          => $product->warehouse_id,
            'products'              => [$order_product],
            'page_checkout'         => $req->header('Referer'),
            'params'                => $req->header('Referer') ? \Utils::getParamsFromUrl($req->header('Referer')) : $req->query() ?? [],
            'offer'                 => $req->get('offerid'),
            'affiliate'             => $req->get('affid'),
            'ipqualityscore'        => $ipqs
        ], true);

        if (isset($reply['errors'])) {
            throw new OrderUpdateException(json_encode($reply['errors']));
        }

        $order = $reply['order'];

        // provider selection in vacuum
        $checkoutService = new CheckoutDotComService();
        $source = CheckoutDotComService::createCardSource($card, $contact);
        $card_3ds = CheckoutDotComService::create3dsObj($card['type'], $contact['country'], $ipqs);
        $payment = $checkoutService->pay($source, $contact, $order, $card_3ds);

        // add Txn, update OdinOrder
        if (!empty($payment['hash'])) {
            (new OrderService())->addTxn([
                'hash'              => $payment['hash'],
                'value'             => $payment['value'],
                'currency'          => $payment['currency'],
                'provider_data'     => $payment['provider_data'],
                'payment_method'    => $card['type'],
                'payment_provider'  => $payment['payment_provider'],
                'payer_id'          => $payment['payer_id']
            ]);

            $order_product['txn_hash'] = $payment['hash'];
            $order->is_flagged = $payment['is_flagged'];
            $order->addProduct($order_product);
            $order->addTxn([
                'hash'              => $payment['hash'],
                'value'             => $payment['value'],
                'status'            => $payment['status'],
                'fee'               => $payment['fee'],
                'payment_method'    => $card['type'],
                'payment_provider'  => $payment['payment_provider'],
                'payer_id'          => $payment['payer_id']
            ]);

            if (!$order->save()) {
                $validator = $order->validate();
                if ($validator->fails()) {
                    throw new OrderUpdateException(json_encode($validator->errors()->all()));
                }
            }
        } else {
            throw new PaymentException(json_encode($payment['provider_data']));
        }

        return [
            'order'         => $order,
            'provider'      => $payment['payment_provider'],
            'status'        => $payment['status'] !== Txn::STATUS_FAILED ? self::STATUS_OK : self::STATUS_FAIL,
            'redirect_url'  => $payment['redirect_url']
        ];
    }

    /**
     * Adds upsells to order using CardToken
     * @param  PaymentCardCreateUpsellsOrderRequest $req
     * @return array
     */
    public function createUpsellsOrder(PaymentCardCreateUpsellsOrderRequest $req)
    {
        $upsells = $req->input('upsells', []);

        $order = OdinOrder::getById($req->get('order')); // throwable
        $order_main_product = $order->getMainProduct(); // throwable
        $order_main_txn = $order->getTxnByHash($order_main_product['txn_hash']); //throwable
        $main_product = OdinProduct::getBySku($order_main_product['sku_code']); // throwable
        $card_token = self::getCardToken($order->getIdAttribute());

        // prepare upsells result
        $upsells = array_map(function($v) {
            $v['status'] = self::STATUS_FAIL;
            return $v;
        }, $upsells);

        if ($this->orderService->checkIfUpsellsPossible($order) && !empty($card_token)) {
            $upsell_products = [];
            $checkout_price = 0;
            $checkout_price_usd = 0;
            foreach ($upsells as $key => $item) {
                try {
                    $product = $this->productService->getUpsellProductById($main_product, $item['id'], $item['qty'], $order->currency); // throwable
                    $upsell_price = $product->upsellPrices[$item['qty']];
                    $upsell_product = [
                        'sku_code'              => $product->upsell_sku,
                        'quantity'              => (int)$item['qty'],
                        'price'                 => $upsell_price['price'],
                        'price_usd'             => floor($upsell_price['price'] / $upsell_price['exchange_rate'] * 100) / 100,
                        'is_main'               => false,
                        'is_upsells'            => true,
                        'price_set'             => null,
                        'warranty_price'        => 0,
                        'warranty_price_usd'    => 0
                    ];
                    $checkout_price += $upsell_product['price'];
                    $checkout_price_usd += $upsell_product['price_usd'];
                    $upsell_products[] = $upsell_product;
                } catch (HttpException $e) {
                    $upsells[$key]['status'] = self::STATUS_FAIL;
                }
            }

            if ($checkout_price >= OdinProduct::MIN_PRICE) {
                // select provider by main txn
                $checkoutService = new CheckoutDotComService();
                $source = CheckoutDotComService::createTokenSource($card_token);
                $payment = $checkoutService->pay($source, ['payer_id' => $order_main_txn['payer_id']], $order, null, $checkout_price);

                // update order if transaction is passed
                if (!empty($payment['hash'])) {
                    (new OrderService())->addTxn([
                        'hash'              => $payment['hash'],
                        'value'             => $payment['value'],
                        'currency'          => $payment['currency'],
                        'provider_data'     => $payment['provider_data'],
                        'payment_method'    => $order_main_txn['payment_method'],
                        'payment_provider'  => $payment['payment_provider'],
                        'payer_id'          => $payment['payer_id']
                    ]);

                    $upsells = array_map(function($v) use ($payment) {
                        $v['status'] = $payment['status'] !== Txn::STATUS_FAILED ? self::STATUS_OK : self::STATUS_FAIL;
                        return $v;
                    }, $upsells);

                    $order->addTxn([
                        'hash'              => $payment['hash'],
                        'value'             => $payment['value'],
                        'status'            => $payment['status'],
                        'fee'               => $payment['fee'],
                        'payment_method'    => $order_main_txn['payment_method'],
                        'payment_provider'  => $payment['payment_provider'],
                        'payer_id'          => $payment['payer_id']
                    ]);

                    // add upsell products
                    foreach ($upsell_products as $item) {
                        $item['txn_hash'] = $payment['hash'];
                        $order->addProduct($item);
                    }

                    if ($order->status === OdinOrder::STATUS_PAID) {
                        $order->status = OdinOrder::STATUS_HALFPAID;
                    }
                    $order->total_price += $checkout_price;
                    $order->total_price_usd += $checkout_price_usd;

                    if (!$order->save()) {
                        $validator = $order->validate();
                        if ($validator->fails()) {
                            throw new OrderUpdateException(json_encode($validator->errors()->all()));
                        }
                    }
                }
            }
        }

        return [
            'order_currency'    => $order->currency,
            'order_id'          => $order->getIdAttribute(),
            'status'            => $order_main_txn['status'] !== Txn::STATUS_FAILED ? self::STATUS_OK : self::STATUS_FAIL,
            'upsells'           => $upsells
        ];
    }

    /**
     * Approves order
     * @param array $data
     */
    public function approveOrder(array $data): void
    {
        $order = OdinOrder::getByNumber($data['number']); // throwable

        // check webhook reply
        if (!in_array($order->status, [OdinOrder::STATUS_NEW, OdinOrder::STATUS_HALFPAID])) {
            return;
        }

        $txn = $order->getTxnByHash($data['hash'], false);
        if ($txn) {
            $txn['status'] = Txn::STATUS_APPROVED;
            $order->addTxn($txn);
        }

        $products = $order->getProductsByTxnHash($data['hash']);
        foreach ($products as $product) {
            $product['is_paid'] = true;
            $order->addProduct($product);
        }

        $currency = CurrencyService::getCurrency($order->currency);

        $order->total_paid += $data['value'];
        $order->total_paid_usd += floor($data['value'] / $currency->usd_rate * 100) / 100;
        $order->status = $order->total_paid >= $order->total_price ? OdinOrder::STATUS_PAID : OdinOrder::STATUS_HALFPAID;

        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }

    }

    /**
     * Requests card token
     * @param  array  $card
     * @param  array  $contacts
     * @param  string $provider default=checkoutcom
     * @return array|null
     */
    public function requestCardToken(array $card, array $contacts, string $provider = self::PROVIDER_CHECKOUTCOM): ?array
    {
        // select provider and request token
        return (new CheckoutDotComService())->requestToken($card, $contacts);
    }


    /**
     * Returns payment methods array by country
     * Results example:
     * $result = [
      self::PROVIDER_CHECKOUTCOM => [
      self::METHOD_VISA => [
      'name' => 'VISA',
      'logo' => 'https://static-backend.saratrkr.com/image_assets/visa-curved-128px.png',
      '3ds' => true
      ]
      ]
      ];
     * @param string $country
     * @return boolean
     */
    public static function getPaymentMethodsByCountry(string $country)
    {
        $result = [];
        foreach (static::$providers as $providerId => $provider)
        {
            if ($provider['is_active'])
            {
                $result[$providerId] = [];

                //check every method of provider
                foreach ($provider['methods'] as $methodId => $method)
                {
                    if (static::$methods[$methodId]['is_active'])
                    {
                        //check 3DS settings
                        if (!empty($method['+3ds']) && static::checkIfMethodInCountries($country, $method['+3ds']))
                        {
                            $result[$providerId][$methodId] = ['3ds' => true];
                        } elseif (!empty($method['-3ds']) && static::checkIfMethodInCountries($country, $method['-3ds']))
                        {
                            $result[$providerId][$methodId] = ['3ds' => false];
                        }

                        //check if country is excluded
                        if (!empty($method['excl']) && static::checkIfMethodInCountries($country, $method['excl']))
                        {
                            unset($result[$providerId][$methodId]);
                        }
                    }
                }
                if ($result[$providerId])
                {
                    foreach ($result[$providerId] as $methodId => &$methodData)
                    {
                        $method             = static::$methods[$methodId];
                        $methodData['name'] = $method['name'];
                        $methodData['logo'] = $method['logo'];
                    }
                } else
                {
                    //no suitable methods found for this provider
                    unset($result[$providerId]);
                }
            }
        }
        return $result;
    }

    /**
     * Returns card token from cache
     * @param string $order_id
     * @param boolean $is_remove default=true
     * @return string|null
     */
    public static function getCardToken(string $order_id, bool $is_remove = true): ?string
    {
        if ($is_remove) {
            return Cache::pull("cardtoken#order_id#{$order_id}");
        }
        return Cache::get("cardtoken#order_id#{$order_id}");
    }

    /**
     * Puts card token to cache
     * @param array $token
     * @param string $order_id
     * @return void
     */
    public static function setCardToken(array $token, string $order_id): void
    {
        if (!empty($token)) {
            $dt = $token['dt'] ?? (new \DateTime())->add(new \DateInterval('PT15M'));
            Cache::put("cardtoken#order_id#{$order_id}", $token['token'], $dt);
        }
    }

    /**
     * Checks if method exists for specified country
     * @param string $country
     * @param array $methodCountries
     * @return bool
     */
    private static function checkIfMethodInCountries(string $country, array $methodCountries): bool
    {
        $result = false;

        $orCountry = '*';
        if (UtilsService::isEUCountry($country))
        {
            $orCountry = 'europe';
        }

        if (
                in_array($country, $methodCountries) ||
                in_array($orCountry, $methodCountries) ||
                in_array('*', $methodCountries)
        )
        {
            $result = true;
        }
        return $result;
    }

}
