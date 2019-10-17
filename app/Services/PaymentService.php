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
use App\Exceptions\ProviderNotFoundException;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Services\CurrencyService;
use App\Services\CustomerService;
use App\Services\CheckoutDotComService;
use App\Services\EbanxService;
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

    const CARD_TOKEN_TTL_MIN    = 15;
    const CARD_TOKEN_PREFIX     = 'CardToken';

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
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_VISA       => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_MASTERCARD => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_AMEX       => [
                    '+3ds' => ['europe', 'by', 'in', 'ko', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*'],
                    'excl' => ['br', 'mx', 'co']
                ],
                self::METHOD_DISCOVER   => [
                    '+3ds' => ['europe', 'by', 'in', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*']
                    // '-3ds' => ['us']
                ],
                self::METHOD_DINERSCLUB => [
                    '+3ds' => ['europe', 'by', 'in', 'il', 'sa', 'ru', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*']
                    // '-3ds' => ['us', 'ko']
                ],
                self::METHOD_JCB        => [
                    '+3ds' => ['europe', 'il', 'ko', 'id', 'id', 'kr', 'gb', 'se'],
                    '-3ds' => ['*']
                    // '-3ds' => ['sg', 'jp', 'tw', 'hk', 'mo', 'th', 'vn', 'kh', 'my', 'mm']
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
            'is_active' => false,
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
     * Updates or adds customer
     * @param array $contact
     * @return void
     * @throws CustomerUpdateException
     */
    private function addCustomer(array $contact): void
    {
        $reply = $this->customerService->addOrUpdate(
            array_merge($contact,
                [
                    'doc_id'    => $contact['document_number'] ?? null,
                    'phone'     => $contact['phone']['country_code'] . $contact['phone']['number']
                ]
            )
        );
        if (isset($reply['errors'])) {
            throw new CustomerUpdateException(json_encode($reply['errors']));
        }
    }

    /**
     * Adds a new OdinOrder
     * @param array $data
     * @return OdinOrder
     * @throws OrderUpdateException
     */
    private function addOrder(array $data): OdinOrder
    {
        $reply = $this->orderService->addOdinOrder(array_merge(['status' => OdinOrder::STATUS_NEW], $data), true);

        if (isset($reply['errors'])) {
            throw new OrderUpdateException(json_encode($reply['errors']));
        }

        return $reply['order'];
    }

    /**
     * Adds txn data to Order
     * @param  OdinOrder &$order
     * @param  array     $data
     * @param  string    $payment_method
     * @return void
     */
    private function addTxnToOrder(OdinOrder &$order, array $data, string $payment_method): void
    {
        // log txn
        (new OrderService())->addTxn([
            'hash'              => $data['hash'],
            'value'             => $data['value'],
            'currency'          => $data['currency'],
            'provider_data'     => $data['provider_data'],
            'payment_method'    => $payment_method,
            'payment_provider'  => $data['payment_provider'],
            'payer_id'          => $data['payer_id']
        ]);

        $order->addTxn([
            'hash'              => $data['hash'],
            'value'             => $data['value'],
            'status'            => $data['status'],
            'fee'               => $data['fee'],
            'payment_method'    => $payment_method,
            'payment_provider'  => $data['payment_provider'],
            'payer_id'          => $data['payer_id']
        ]);
    }

    /**
     * Returns localizaed price
     * @param  OdinProduct $product
     * @param  int         $qty
     * @return array
     * @throws InvalidParamsException
     */
    private function getLocalizedPrice(OdinProduct $product, int $qty): array
    {
        $localized_product = $this->productService->localizeProduct($product);
        if (empty($localized_product->prices[$qty])) {
            throw new InvalidParamsException('Invalid parameter "qty"');
        }

        $currency = CurrencyService::getCurrency($localized_product->prices['currency']);

        $price_usd = floor($localized_product->prices[$qty]['value'] / $currency->usd_rate * 100) / 100;

        return [
            'currency'          => $currency->code,
            'price_set'         => $product->prices['price_set'],
            'quantity'          => $qty,
            'usd_rate'          => $currency->usd_rate,
            'value'             => $localized_product->prices[$qty]['value'],
            'value_usd'         => $price_usd,
            'warranty_value'    => $localized_product->prices[$qty]['warranty_price'],
            'warranty_value_usd'    => CurrencyService::calculateWarrantyPrice($product->warranty_percent, $price_usd)
        ];
    }

    /**
     * Creates OdinOrder['products'] item
     * @param  string      $sku
     * @param  array       $price
     * @param  bool        $is_main
     * @param  bool        $is_warranty
     * @return array
     * @throws InvalidParamsException
     */
    private function createOrderProduct(string $sku, array $price, bool $is_main = true, bool $is_warranty = false): array
    {
        $order_product = [
            'sku_code'              => $sku,
            'quantity'              => $price['quantity'],
            'price'                 => $price['value'],
            'price_usd'             => $price['value_usd'],
            'price_set'             => $price['price_set'] ?? null,
            'is_main'               => $is_main,
            'is_upsells'            => !$is_main,
            'txn_hash'              => null,
            'warranty_price'        => 0,
            'warranty_price_usd'    => 0,
            'total_price'           => $price['value'],
            'total_price_usd'       => $price['value_usd']
        ];

        if ($is_warranty) {
            $order_product['warranty_price']        = $price['warranty_value'];
            $order_product['warranty_price_usd']    = $price['warranty_value_usd'];
            $order_product['total_price']           = floor(($price['value'] + $price['warranty_value']) * 100) / 100;
            $order_product['total_price_usd']       = floor(($price['value_usd'] + $price['warranty_value_usd']) * 100) / 100;
        }
        return $order_product;
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
        $installments = (int)$req->input('product.installments', 0);
        $contact = array_merge($req->get('contact'), $req->get('address'));
        $page_checkout = $req->input('page_checkout', $req->header('Referer'));
        $ipqs = $req->input('ipqs', null);
        $cur = $req->get('cur');
        $card = $req->get('card');
        $card['type'] = self::getMethodByNumber($card['number']);

        $product = OdinProduct::getBySku($sku); // throwable

        // select provider by country
        $provider = self::getProviderByCountryAndMethod($contact['country'], $card['type']);
        if (!$provider) {
            throw new ProviderNotFoundException("Country {$contact['country']}, Card {$card['type']} not supported");
        } else if ($provider === self::PROVIDER_EBANX) {
            // check if ebanx supports country and currency, switch to default currency
            $product->currency = EbanxService::getCurrencyByCountry($contact['country'], $cur);
            if (!$product->currency) {
                // change provider
                $provider = self::getProviderByCountryAndMethod($contact['country'], $card['type'], [self::PROVIDER_EBANX]);
                if (!$provider) {
                    throw new ProviderNotFoundException("Country {$contact['country']}, Card {$card['type']} not supported");
                }
            }
        }

        $this->addCustomer($contact); // throwable

        $price = $this->getLocalizedPrice($product, (int)$qty); // throwable

        $order_product = $this->createOrderProduct($sku, $price, true, $is_warranty);
        
        $params = !empty($page_checkout) ? \Utils::getParamsFromUrl($page_checkout) : null;

        $order = $this->addOrder([
            'currency'              => $price['currency'],
            'exchange_rate'         => $price['usd_rate'],
            'total_paid'            => 0,
            'total_price'           => $order_product['total_price'],
            'total_price_usd'       => $order_product['total_price_usd'],
            'txns_fee_usd'          => 0,
            'installments'          => $installments,
            'customer_email'        => $contact['email'],
            'customer_first_name'   => $contact['first_name'],
            'customer_last_name'    => $contact['last_name'],
            'customer_phone'        => $contact['phone']['country_code'] . $contact['phone']['number'],
            'customer_doc_id'       => $contact['document_number'] ?? null,
            'language'              => app()->getLocale(),
            'ip'                    => $req->ip(),
            'txns'                  => [],
            'shipping_country'      => $contact['country'],
            'shipping_zip'          => $contact['zip'],
            'shipping_state'        => $contact['state'],
            'shipping_city'         => $contact['city'],
            'shipping_street'       => $contact['street'],
            'shipping_street2'      => $contact['district'] ?? null,
            'shop_currency'         => $price['currency'],
            'warehouse_id'          => $product->warehouse_id,
            'products'              => [$order_product],
            'page_checkout'         => $page_checkout,
            'params'                => \Utils::getParamsFromUrl($page_checkout),
            'offer'                 => !empty($params['offer_id']) ? $params['offer_id'] : null,,
            'affiliate'             => !empty($params['aff_id']) ? $params['aff_id'] : null,,
            'ipqualityscore'        => $ipqs
        ]);

        // select provider and create payment
        $payment = [];
        if ($provider === self::PROVIDER_EBANX) {
            $ebanxService = new EbanxService();
            $payment = $ebanxService->payByCard($card, $contact, [
                'amount'        => $order->total_price,
                'currency'      => $order->currency,
                'number'        => $order->number,
                'installments'  => $installments
            ]);
        } else {
            $checkoutService = new CheckoutDotComService();
            $source = CheckoutDotComService::createCardSource($card, $contact);
            $payment = $checkoutService->pay($source, $contact, $order, self::checkIs3dsNeeded($card['type'], $contact['country'], $ipqs));
            if ($payment['status'] !== Txn::STATUS_FAILED) {
                $payment['token'] = $checkoutService->requestToken($card, $contact);
            }
        }

        // cache token
        self::setCardToken($order->number, $payment['token']);

        // add Txn, update OdinOrder
        if (!empty($payment['hash'])) {
            $order_product['txn_hash'] = $payment['hash'];
            $this->addTxnToOrder($order, $payment, $card['type']);
            $order->addProduct($order_product);
            $order->is_flagged = $payment['is_flagged'];
            if (!$order->save()) {
                $validator = $order->validate();
                if ($validator->fails()) {
                    throw new OrderUpdateException(json_encode($validator->errors()->all()));
                }
            }
        } else {
            throw new PaymentException(json_encode($payment['provider_data']));
        }

        // response
        $result = [
            'order_currency'    => $order->currency,
            'order_number'      => $order->number,
            'order_id'          => $order->getIdAttribute(),
            'id'                => !empty($payment['hash']) ? $payment['hash'] : '',
            'status'            => self::STATUS_FAIL
        ];

        if ($payment['status'] !== Txn::STATUS_FAILED) {
            $result['status'] = self::STATUS_OK;
            $result['redirect_url'] = $payment['redirect_url'] ?? null;
        } else {
            $result['status_code'] = $payment['response_code'];
            $result['status_desc'] = $payment['response_desc'];
        }

        return $result;
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
        $card_token = self::getCardToken($order->number);

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
                    $upsell_product = $this->createOrderProduct(
                        $product->upsell_sku,
                        [
                            'quantity'  => (int)$item['qty'],
                            'value'     => $upsell_price['price'],
                            'value_usd' => floor($upsell_price['price'] / $upsell_price['exchange_rate'] * 100) / 100
                        ],
                        false
                    );
                    $checkout_price += $upsell_product['total_price'];
                    $checkout_price_usd += $upsell_product['total_price_usd'];
                    $upsell_products[] = $upsell_product;
                } catch (HttpException $e) {
                    $upsells[$key]['status'] = self::STATUS_FAIL;
                }
            }

            if ($checkout_price >= OdinProduct::MIN_PRICE) {
                // select provider by main txn
                if ($order_main_txn['payment_provider'] === self::PROVIDER_EBANX) {
                    $ebanxService = new EbanxService();
                    $payment = $ebanxService->payByToken($card_token, [
                        'street'            => $order->shipping_street,
                        'city'              => $order->shipping_city,
                        'country'           => $order->shipping_country,
                        'state'             => $order->shipping_state,
                        'district'          => $order->shipping_street2,
                        'zip'               => $order->shipping_zip,
                        'document_number'   => $order->customer_doc_id,
                        'email'             => $order->customer_email,
                        'first_name'        => $order->customer_first_name,
                        'last_name'         => $order->customer_last_name,
                        'phone'             => $order->customer_phone
                    ], [
                        'amount'        => $checkout_price,
                        'currency'      => $order->currency,
                        'number'        => $order->number,
                        'installments'  => $order->installments
                    ]);
                } else {
                    $checkoutService = new CheckoutDotComService();
                    $source = CheckoutDotComService::createTokenSource($card_token);
                    $payment = $checkoutService->pay($source, ['payer_id' => $order_main_txn['payer_id']], $order, null, $checkout_price);
                }

                // update order if transaction is passed
                if (!empty($payment['hash'])) {

                    $upsells = array_map(function($v) use ($payment) {
                        $v['status'] = $payment['status'] !== Txn::STATUS_FAILED ? self::STATUS_OK : self::STATUS_FAIL;
                        $v['status_code'] = $payment['response_code'];
                        $v['status_desc'] = $payment['response_desc'];
                        return $v;
                    }, $upsells);

                    // add upsell products
                    foreach ($upsell_products as $item) {
                        $item['txn_hash'] = $payment['hash'];
                        $order->addProduct($item);
                    }

                    $this->addTxnToOrder($order, $payment, $order_main_txn['payment_method']);

                    if ($order->status === OdinOrder::STATUS_PAID) {
                        $order->status = OdinOrder::STATUS_HALFPAID;
                    }
                    $order->total_price += $checkout_price;
                    $order->total_price_usd += $checkout_price_usd;
                    $order->is_invoice_sent = false;

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
            'order_number'      => $order->number,
            'order_id'          => $order->getIdAttribute(),
            'id'                => isset($payment) && !empty($payment['hash']) ? $payment['hash'] : $order_main_product['txn_hash'],
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
            $txn['fee'] = $data['fee'];
            $txn['status'] = $data['status'];
            $order->addTxn($txn);
        }

        if ($txn && $txn['status'] === Txn::STATUS_APPROVED) {
            $products = $order->getProductsByTxnHash($data['hash']);
            foreach ($products as $product) {
                $product['is_paid'] = true;
                $order->addProduct($product);
            }

            $currency = CurrencyService::getCurrency($order->currency);

            $order->total_paid      = floor(($order->total_paid + $data['value']) * 100) / 100;
            $order->total_paid_usd  = floor(($order->total_paid_usd + $data['value'] / $currency->usd_rate) * 100) / 100;
            $order->txns_fee_usd    = floor(($order->txns_fee_usd + $data['fee'] / $currency->usd_rate) * 100) / 100;

            $price_paid_diff    = floor(($order->total_paid - $order->total_price) * 100) / 100;
            $order->status      = $price_paid_diff >= 0 ? OdinOrder::STATUS_PAID : OdinOrder::STATUS_HALFPAID;
        }

        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }

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
     * Returns available provider for country and payment method
     * @param   string $country
     * @param   string $method
     * @param   array  $excl
     * @return  string|null
     */
    public static function getProviderByCountryAndMethod(string $country, string $method, array $excl = []): ?string
    {
        $providers = self::getPaymentMethodsByCountry($country);

        $result = null;
        foreach ($providers as $prv => $methods) {
            if (isset($methods[$method]) && !in_array($prv, $excl)) {
                $result = $prv;
                break;
            }
        }
        return $result;
    }

    /**
     * Returns card token from cache
     * @param string $order_number
     * @param boolean $is_remove default=true
     * @return string|null
     */
    public static function getCardToken(string $order_number, bool $is_remove = true): ?string
    {
        if ($is_remove) {
            return Cache::pull(self::CARD_TOKEN_PREFIX . $order_number);
        }
        return Cache::get(self::CARD_TOKEN_PREFIX . $order_number);
    }

    /**
     * Puts card token to cache
     * @param string $order_number
     * @param string|null $token
     * @return void
     */
    public static function setCardToken(string $order_number, ?string $token): void
    {
        if ($token) {
            $dt = (new \DateTime())->add(new \DateInterval("PT" . self::CARD_TOKEN_TTL_MIN . "M"));
            Cache::put(self::CARD_TOKEN_PREFIX . $order_number, $token, $dt);
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

    /**
     * Checks if 3ds is available
     * @param  string $card_type
     * @param  string $country
     * @param  array|null $ipqs
     * @return object
     */
    private static function checkIs3dsNeeded(string $card_type, string $country, ?array $ipqs): bool
    {
        $result = true;
        $setting = PaymentService::$providers[PaymentService::PROVIDER_CHECKOUTCOM]['methods'][$card_type] ?? [];
        $fraud_chance = !empty($ipqs) ? (int)$ipqs['fraud_chance'] : PaymentService::FRAUD_CHANCE_MAX;

        if (in_array($country, $setting['+3ds'] ?? []) || $fraud_chance > PaymentService::FRAUD_CHANCE_LIMIT) {
            $result = true;
        } else if (in_array($country, $setting['-3ds'] ?? []) && $fraud_chance < PaymentService::FRAUD_CHANCE_LIMIT) {
            $result = false;
        }

        return $result;
    }

    /**
     * Returns Credit Card type
     * @param  string $number
     * @return string
     */
    public static function getMethodByNumber(string $number): string
    {
        $card_masks = [
            [
                'mask' => '/^4[0-9]{0,15}$/i',
                'type' => self::METHOD_VISA
            ],
            [
                'mask' => '/^5[1-5][0-9]{5,}|^222[1-9][0-9]{3,}|^22[3-9][0-9]{4,}|^2[3-6][0-9]{5,}|^27[01][0-9]{4,}|^2720[0-9]{3,}$/i',
                'type' => self::METHOD_MASTERCARD
            ],
            [
                'mask' => '/^3$|^3[47][0-9]{0,13}$/i',
                'type' => self::METHOD_AMEX
            ],
            [
                'mask' => '/^6$|^6[05]$|^601[1]?$|^65[0-9][0-9]?$|^6(?:011|5[0-9]{2})[0-9]{0,12}$/i',
                'type' => self::METHOD_DISCOVER
            ],
            [
                'mask' => '/^(?:2131|1800|35[0-9]{3})[0-9]{3,}$/i',
                'type' => self::METHOD_JCB
            ],
            [
                'mask' => '/^3(?:0[0-5]|[68][0-9])[0-9]{4,}$/i',
                'type' => self::METHOD_DINERSCLUB
            ],
            [
                'mask' => '/^((606282)|(637095)|(637568)|(637599)|(637609)|(637612))/i',
                'type' => self::METHOD_HIPERCARD
            ],
            [
                'mask' => '/^((509091)|(636368)|(636297)|(504175)|(438935)|(40117[8-9])|(45763[1-2])|(457393)|(431274)|(50990[0-2])|'
                        . '(5099[7-9][0-9])|(50996[4-9])|(509[1-8][0-9][0-9])|(5090(0[0-2]|0[4-9]|1[2-9]|[24589][0-9]|3[1-9]|6[0-46-9]|7[0-24-9]))|'
                        . '(5067(0[0-24-8]|1[0-24-9]|2[014-9]|3[0-379]|4[0-9]|5[0-3]|6[0-5]|7[0-8]))|(6504(0[5-9]|1[0-9]|2[0-9]|3[0-9]))|'
                        . '(6504(8[5-9]|9[0-9])|6505(0[0-9]|1[0-9]|2[0-9]|3[0-8]))|(6505(4[1-9]|5[0-9]|6[0-9]|7[0-9]|8[0-9]|9[0-8]))|'
                        . '(6507(0[0-9]|1[0-8]))|(65072[0-7])|(6509(0[1-9]|1[0-9]|20))|(6516(5[2-9]|6[0-9]|7[0-9]))|(6550(0[0-9]|1[0-9]))|'
                        . '(6550(2[1-9]|3[0-9]|4[0-9]|5[0-8])))/i',
                'type' => self::METHOD_ELO
            ],
            [
                'mask' => '/^(5078\d{2})(\d{2})(\d{11})$/i',
                'type' => self::METHOD_AURA
            ]
        ];

        $result = self::METHOD_CREDITCARD;

        foreach ($card_masks as $item) {
            if (preg_match($item['mask'], $number)) {
                $result = $item['type'];
                break;
            }
        }

        return $result;
    }

    public function testEbanxUpsells(PaymentCardCreateUpsellsOrderRequest $req)
    {
        return ['msg' => '@todo upsells'];
    }
}
