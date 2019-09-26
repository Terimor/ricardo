<?php

namespace App\Services;

use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Exceptions\CustomerUpdateException;
use App\Exceptions\InvalidParamsException;
use App\Exceptions\OrderUpdateException;
use App\Exceptions\PaymentException;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Services\CurrencyService;
use App\Services\CustomerService;
use App\Services\CheckoutDotComService;
use App\Services\OrderService;

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
     *
     */
    public function createOrder(PaymentCardCreateOrderRequest $req)
    {
        ['sku' => $sku, 'qty' => $qty] = $req->get('product');
        $address = $req->get('address');
        $card = $req->get('card');
        $contact = $req->get('contact');

        // get product and localize it
        $product = $this->productService->getBySku($sku);
        $localizedProduct = $this->productService->localizeProduct($product); // throwable

        // update customer if it has been changed
        $reply = $this->customerService->addOrUpdate(
            array_merge($address, $contact, ['phone' => $contact['phone']['country_code'] + $contact['phone']['number']])
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
            'sku_code' => $sku,
            'quantity' => (int)$qty,
            'price' => $price['value'],
            'price_usd' => floor($price['value'] / $currency->usd_rate * 100) / 100,
            'is_main' => true,
            'price_set' => $product->prices['price_set'],
        ];

        $reply = $this->orderService->addOdinOrder([
            'status' => OdinOrder::STATUS_NEW,
            'currency' => $currency->code,
            'exchange_rate' => $currency->usd_rate,
            'total_paid' => 0,
            'total_price' => $order_product['price'],
            'total_price_usd' => $order_product['price_usd'],
            "txns_fee_usd" => 0,
            'installments' => 0,
            'customer_email' => $contact['email'],
            'customer_first_name' => $contact['first_name'],
            'customer_last_name' => $contact['last_name'],
            'customer_phone' => $contact['phone'],
            'language' => app()->getLocale(),
            'ip' => $req->ip(),
            'txns' => [],
            'shipping_country' => $address['country'],
            'shipping_zip' => $address['zip'],
            'shipping_state' => $address['state'],
            'shipping_city' => $address['city'],
            'shipping_street' => $address['street'],
            'warehouse_id' => $product->warehouse_id,
            'products' => [$order_product],
            'page_checkout' => $req->fullUrl(),
            'params' => !empty($req->query()) ? $req->query : null
        ], true);

        if (isset($reply['errors'])) {
            throw new OrderUpdateException(json_encode($reply['errors']));
        }

        $order = $reply['order'];

        // provider selection in vacuum
        $checkoutService = new CheckoutDotComService();
        $reply = $checkoutService->pay($card, array_merge($address, $contact), $order);

        // add Txn, update OdinOrder
        if (!empty($reply['hash'])) {
            (new OrderService())->addTxn([
                'hash' => $reply['hash'],
                'value' => $reply['value'],
                'currency' => $reply['currency'],
                'provider_data' => $reply['provider_data'],
                'payment_method' => $reply['payment_method'],
                'payment_provider' => $reply['payment_provider'],
                'payer_id' => $reply['payer_id']
            ]);

            $order->is_flagged = $reply['is_flagged'];
            $order->txns = array_merge($order->txns, [
                [
                    'hash' => $reply['hash'],
                    'value' => $reply['value'],
                    'status' => $reply['status'],
                    'fee' => $reply['fee'],
                    'payment_method' => $reply['payment_method'],
                    'payment_provider' => $reply['payment_provider'],
                    'payer_id' => $reply['payer_id']
                ]
            ]);

            if (!$order->save()) {
                $validator = $order->validate();
                if ($validator->fails()) {
                    throw new OrderUpdateException(json_encode($validator->errors()->all()));
                }
            }
        } else {
            throw new PaymentException(json_encode($reply['provider_data']));
        }

        return [
            'order_currency' => $order->currency,
            'order_id' => $order->getIdAttribute(),
            'status' => $reply['status'] === Txn::STATUS_CAPTURED ? 'ok' : 'fail'
        ];
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
