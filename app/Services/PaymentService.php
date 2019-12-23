<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Http\Requests\PaymentCardMinte3dsRequest;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use Illuminate\Http\Request;
use App\Exceptions\CustomerUpdateException;
use App\Exceptions\ProviderNotFoundException;
use App\Exceptions\InvalidParamsException;
use App\Exceptions\AuthException;
use App\Exceptions\PaymentException;
use App\Exceptions\OrderUpdateException;
use App\Models\Txn;
use App\Models\Currency;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Services\BluesnapService;
use App\Services\CurrencyService;
use App\Services\CustomerService;
use App\Services\AppmaxService;
use App\Services\CheckoutDotComService;
use App\Services\EbanxService;
use App\Services\MinteService;
use App\Services\OrderService;
use App\Services\AffiliateService;
use App\Mappers\PaymentMethodMapper;
use App\Constants\PaymentProviders;
use App\Constants\PaymentMethods;
use Http\Client\Exception\HttpException;

/**
 * Payment Service class
 */
class PaymentService
{
    const CARD_CREDIT = 'credit';
    const CARD_DEBIT  = 'debit';

    const FRAUD_CHANCE_MAX  = 100;

    const THROW_IS_IP_ABUSED    = true;

    const SUCCESS_PATH  =   '/checkout';
    const FAILURE_PATH  =   '/checkout';

    const STATUS_OK     = 'ok';
    const STATUS_FAIL   = 'fail';

    const CACHE_TOKEN_PREFIX    = 'CardToken';
    const CACHE_ERRORS_PREFIX   = 'PayErrors';
    const CACHE_TOKEN_TTL_MIN   = 15;
    const CACHE_ERRORS_TTL_MIN  = 1;

    const BILLING_DESCRIPTOR_MAX_LENGTH = 20;
    const BILLING_DESCRIPTOR_COUNTRIES = ['us', 'ca'];
    const BILLING_DESCRIPTOR_COUNTRIES_CODE = '888-743-8103';

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
     * @param  OdinOrder    &$order
     * @param  array        $data
     * @param  array        $details
     * @return void
     */
    private function addTxnToOrder(OdinOrder &$order, array $data, array $details): void
    {
        // log txn
        (new OrderService())->addTxn([
            'hash'              => $data['hash'],
            'value'             => $data['value'],
            'currency'          => $data['currency'],
            'provider_data'     => $data['provider_data'],
            'payment_method'    => $details['payment_method'],
            'payment_provider'  => $data['payment_provider'],
            'payer_id'          => $data['payer_id'] ?? null
        ]);

        $order->addTxn([
            'hash'              => (string)$data['hash'],
            'value'             => $data['value'],
            'status'            => $data['status'],
            'fee_usd'           => $data['fee_usd'] ?? 0,
            'card_type'         => $details['card_type'] ?? null,
            'card_number'       => $details['card_number'],
            'payment_method'    => $details['payment_method'],
            'payment_provider'  => $data['payment_provider'],
            'payment_api_id'    => $data['payment_api_id'] ?? null,
            'payer_id'          => $data['payer_id'] ?? null
        ]);
    }

    /**
     * Returns localizaed price
     * @param  OdinProduct $product
     * @param  int         $qty
     * @param  string      $country
     * @param  string      $provider
     * @return array
     * @throws InvalidParamsException
     */
    private function getLocalizedPrice(OdinProduct $product, int $qty, string $country, string $provider): array
    {
        // NOTE: implicit definition currency
        $localized_product = $this->productService->localizeProduct($product);
        if (empty($localized_product->prices[$qty])) {
            throw new InvalidParamsException('Invalid parameter "qty"');
        }

        $currency = CurrencyService::getCurrency($localized_product->prices['currency']);

        $price = $localized_product->prices[$qty]['value'];
        $price_usd = $price / $currency->usd_rate;
        $price_warranty = $localized_product->prices[$qty]['warranty_price'];
        $price_warranty_usd = ($product->warranty_percent ?? 0) * $price_usd / 100;
        $usd_rate = $currency->usd_rate;

        // check currency, if it's not supported switch to default currency
        $currency_code = $this->checkCurrency($country, $localized_product->prices['currency'], $provider);

        if ($currency_code === Currency::DEF_CUR) {
            $price = $price_usd;
            $price_warranty = $price_warranty_usd;
            $usd_rate = 1;
        }

        return [
            'currency'          => $currency_code,
            'price_set'         => $product->prices['price_set'] ?? '',
            'quantity'          => $qty,
            'usd_rate'          => $usd_rate,
            'value'             => $price,
            'value_usd'         => $price_usd,
            'warranty_value'    => $price_warranty,
            'warranty_value_usd'    => $price_warranty_usd
        ];
    }

    /**
     * Creates OdinOrder['products'] item
     * @param  string      $sku
     * @param  array       $price
     * @param  array       $details [is_main => bool, is_plus_one => bool, is_warranty => bool]
     * @return array
     * @throws InvalidParamsException
     */
    private function createOrderProduct(string $sku, array $price, array $details = []): array
    {
        $is_main = $details['is_main'] ?? true;
        $order_product = [
            'sku_code'              => $sku,
            'quantity'              => $price['quantity'],
            'price'                 => CurrencyService::roundValueByCurrencyRules($price['value'], $price['currency']),
            'price_usd'             => CurrencyService::roundValueByCurrencyRules($price['value_usd'], Currency::DEF_CUR),
            'price_set'             => $price['price_set'] ?? null,
            'is_main'               => $is_main,
            'is_upsells'            => !$is_main,
            'is_paid'               => false,
            'is_exported'           => false,
            'is_plus_one'           => $details['is_plus_one'] ?? false,
            'txn_hash'              => null,
            'warranty_price'        => 0,
            'warranty_price_usd'    => 0,
            'total_price'           => CurrencyService::roundValueByCurrencyRules($price['value'], $price['currency']),
            'total_price_usd'       => CurrencyService::roundValueByCurrencyRules($price['value_usd'], Currency::DEF_CUR)
        ];

        $is_warranty = $details['is_warranty'] ?? false;
        if ($is_warranty) {
            $order_product['warranty_price']        = CurrencyService::roundValueByCurrencyRules($price['warranty_value'], $price['currency']);
            $order_product['warranty_price_usd']    = CurrencyService::roundValueByCurrencyRules($price['warranty_value_usd'], Currency::DEF_CUR);
            $order_product['total_price']           = CurrencyService::roundValueByCurrencyRules($price['value'] + $price['warranty_value'], $price['currency']);
            $order_product['total_price_usd']       = CurrencyService::roundValueByCurrencyRules($order_product['total_price'] / $price['usd_rate'], Currency::DEF_CUR);
        }
        return $order_product;
    }

    /**
     * Captures payment
     * @param  string $order_id
     * @param  string $txn_hash
     * @return bool
     */
    public function capture(string $order_id, string $txn_hash): bool
    {
        $order = OdinOrder::getById($order_id); //throwable
        $txn = $order->getTxnByHash($txn_hash); //throwable

        $result = false;
        if ($txn['status'] === Txn::STATUS_AUTHORIZED) {
            if ($txn['payment_provider'] === PaymentProviders::CHECKOUTCOM) {
                $checkout = new CheckoutDotComService($txn);
                $result = $checkout->capture($txn_hash);

                if ($result) {
                    $txn['status'] = Txn::STATUS_CAPTURED;
                    $order->addTxn($txn);
                    if (!$order->save()) {
                        $validator = $order->validate();
                        if ($validator->fails()) {
                            throw new OrderUpdateException(json_encode($validator->errors()->all()));
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Voids payment
     * @param  string $order_id
     * @param  string $txn_hash
     * @return bool
     */
    public function void(string $order_id, string $txn_hash): bool
    {
        $order = OdinOrder::getById($order_id); //throwable
        $txn = $order->getTxnByHash($txn_hash); //throwable

        $result = false;
        if ($txn['status'] === Txn::STATUS_AUTHORIZED) {
            if ($txn['payment_provider'] === PaymentProviders::CHECKOUTCOM) {
                $checkout = new CheckoutDotComService($txn);
                $result = $checkout->void($txn_hash);

                if ($result) {
                    $txn['status'] = Txn::STATUS_FAILED;
                    $order->addTxn($txn);
                    if (!$order->save()) {
                        $validator = $order->validate();
                        if ($validator->fails()) {
                            throw new OrderUpdateException(json_encode($validator->errors()->all()));
                        }
                    }
                }
            }
        }
        return $result;
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
        $page_checkout = $req->input('page_checkout', $req->header('Referer'));
        $user_agent = $req->header('User-Agent');
        $ipqs = $req->input('ipqs', null);
        $card = $req->get('card');
        $order_id = $req->get('order');
        $installments = (int)$req->input('card.installments', 0);
        $contact = array_merge(
            $req->get('contact'),
            $req->get('address'),
            ['ip' => $req->ip(), 'email' => strtolower($req->input('contact.email'))]
        );
        $method = PaymentMethodMapper::toMethod($card['number']);

        // find order for update
        $order = null;
        if (!empty($order_id)) {
            $order = OdinOrder::findExistedOrderForPay($order_id, $req->get('product'));
        }

        $product = null;
        if ($req->get('cop_id')) {
            $product = OdinProduct::getByCopId($req->get('cop_id'));
        }
        if (!$product) {
            $product = OdinProduct::getBySku($sku); // throwable
        }

        // select provider by country
        $provider = self::getProviderByCountryAndMethod($contact['country'], $method);
        if (!$provider) {
            logger()->warning(
                'Provider not found',
                [
                    'country' => $contact['country'],
                    'method' => $method,
                    'card' => UtilsService::prepareCardNumber($card['number'])
                ]
            );
            throw new ProviderNotFoundException('Provider not found');
        }

        self::fraudCheck($ipqs, $provider); // throwable

        $this->addCustomer($contact); // throwable

        if (empty($order)) {;
            $price = $this->getLocalizedPrice($product, (int)$qty, $contact['country'], $provider); // throwable

            $order_product = $this->createOrderProduct($sku, $price, ['is_warranty' => $is_warranty]);

            $params = !empty($page_checkout) ? UtilsService::getParamsFromUrl($page_checkout) : null;
            $affId = AffiliateService::getAttributeByPriority($params['aff_id'] ?? null, $params['affid'] ?? null);
            $offerId = AffiliateService::getAttributeByPriority($params['offer_id'] ?? null, $params['offerid'] ?? null);
            $validTxid = AffiliateService::getValidTxid($params['txid'] ?? null);

            $order = $this->addOrder([
                'billing_descriptor'    => $product->getPaymentBillingDescriptor($contact['country']),
                'currency'              => $price['currency'],
                'exchange_rate'         => $price['usd_rate'],
                'total_paid'            => 0,
                'total_paid_usd'        => 0,
                'total_price'           => $order_product['total_price'],
                'total_price_usd'       => $order_product['total_price_usd'],
                'txns_fee_usd'          => 0,
                'installments'          => $installments,
                'is_reduced'            => false,
                'is_invoice_sent'       => false,
                'is_survey_sent'        => false,
                'is_flagged'            => false,
                'is_refunding'          => false,
                'is_refunded'           => false,
                'is_qc_passed'          => false,
                'customer_email'        => $contact['email'],
                'customer_first_name'   => $contact['first_name'],
                'customer_last_name'    => $contact['last_name'],
                'customer_phone'        => $contact['phone']['country_code'] . UtilsService::preparePhone($contact['phone']['number']),
                'customer_doc_id'       => $contact['document_number'] ?? null,
                'ip'                    => $contact['ip'],
                'language'              => app()->getLocale(),
                'txns'                  => [],
                'shipping_country'      => $contact['country'],
                'shipping_zip'          => $contact['zip'],
                'shipping_state'        => $contact['state'],
                'shipping_city'         => $contact['city'],
                'shipping_street'       => $contact['street'],
                'shipping_street2'      => $contact['district'] ?? null,
                'shop_currency'         => CurrencyService::getCurrency()->code,
                'warehouse_id'          => $product->warehouse_id,
                'products'              => [$order_product],
                'page_checkout'         => $page_checkout,
                'params'                => $params,
                'offer'                 => $offerId,
                'affiliate'             => $affId,
                'txid'                  => $validTxid,
                'ipqualityscore'        => $ipqs
            ]);
        } else {
            $order_product = $order->getMainProduct(); // throwable
            $order->customer_email      = $contact['email'];
            $order->customer_first_name = $contact['first_name'];
            $order->customer_last_name  = $contact['last_name'];
            $order->customer_phone      = $contact['phone']['country_code'] . UtilsService::preparePhone($contact['phone']['number']);
            $order->customer_doc_id     = $contact['document_number'] ?? null;
            $order->shipping_country    = $contact['country'];
            $order->shipping_zip        = $contact['zip'];
            $order->shipping_state      = $contact['state'];
            $order->shipping_city       = $contact['city'];
            $order->shipping_street     = $contact['street'];
            $order->shipping_street2    = $contact['district'] ?? null;
            $order->installments        = $installments;
        }
        // select provider and create payment
        $payment = [];
        if ($provider === PaymentProviders::APPMAX) {
            $appmax = new AppmaxService(['product_id' => $product->getIdAttribute()]);
            $payment = $appmax->payByCard(
                $card,
                $contact,
                [
                    [
                        'sku'   => $sku,
                        'qty'   => $qty,
                        'name'  => $product->product_name,
                        'desc'  => $product->long_name,
                        'image' => $product->logo_image,
                        'amount'    => $order->total_price,
                    ]
                ],
                [
                    'amount'        => $order->total_price,
                    'order_id'      => $order->getIdAttribute(),
                    'currency'      => $order->currency,
                    'installments'  => $installments,
                    'document_number' => $order->customer_doc_id
                ]
            );
        } elseif ($provider === PaymentProviders::EBANX) {
            $ebanx = new EbanxService();
            $payment = $ebanx->payByCard(
                $card,
                $contact,
                [
                    [
                        'sku'   => $sku,
                        'qty'   => $qty,
                        'name'  => $product->product_name,
                        'desc'  => $product->description,
                        'amount'    => $order->total_price,
                        'is_main'   => true
                    ]
                ],
                [
                    'amount'        => $order->total_price,
                    'currency'      => $order->currency,
                    'number'        => $order->number,
                    'installments'  => $installments,
                    'product_id'    => $product->getIdAttribute()
                ]
            );
        } elseif ($provider === PaymentProviders::CHECKOUTCOM) {
            $checkout = new CheckoutDotComService(['product_id' => $product->getIdAttribute()]);
            $payment = $checkout->payByCard($card, $contact, [
                'amount'    => $order->total_price,
                'currency'  => $order->currency,
                'id'        => $order->getIdAttribute(),
                'number'    => $order->number,
                '3ds'       => self::checkIs3dsNeeded($method, $contact['country'], $provider, (array)$ipqs),
                'description'   => $product->product_name,
                // TODO: remove city hardcode
                'billing_descriptor'   => ['name' => $order->billing_descriptor, 'city' => 'Msida']
            ]);
            if ($payment['status'] !== Txn::STATUS_FAILED) {
                $payment['token'] = $checkout->requestToken($card, $contact);
            }
        } else if ($provider === PaymentProviders::BLUESNAP) {
            $bluesnap = new BluesnapService();
            $payment = $bluesnap->payByCard(
                $card,
                $contact,
                [
                    'amount'        => $order->total_price,
                    'currency'      => $order->currency,
                    'number'        => $order->number,
                    'billing_descriptor'   => $order->billing_descriptor,
                    'product_id'    => $product->getIdAttribute()
                ]
            );
            logger()->info("Bluesnap card data", ['order' => $order->number, 'card' => $card]);
        } else if ($provider === PaymentProviders::MINTE) {
            $mint = new MinteService();
            $payment = $mint->payByCard($card, $contact, [
                '3ds'       => self::checkIs3dsNeeded($method, $contact['country'], $provider, (array)$ipqs),
                'amount'    => $order->total_price,
                'currency'  => $order->currency,
                'order_id'  => $order->getIdAttribute(),
                'order_number'  => $order->number,
                'product_id'    => $product->getIdAttribute(),
                'user_agent'    => $user_agent,
                'descriptor'    => $order->billing_descriptor
            ]);
        }

        $this->addTxnToOrder($order, $payment, [
            'payment_method' => $method,
            'card_number' => UtilsService::prepareCardNumber($card['number']),
            'card_type' => $card['type'] ?? null
        ]);

        $order_product['txn_hash'] = $payment['hash'];
        $order->addProduct($order_product, true);

        // check is this fallback
        if (!empty($payment['fallback'])) {

            $reply = $this->fallbackOrder($order, $card, [
                'provider'   => $provider,
                'product_id' => $product->getIdAttribute(),
                'method'     => $method,
                'useragent'  => $user_agent
            ]);

            if ($reply['status']) {
                $payment = $reply['payment'];
                $order_product = $order->getMainProduct(); // throwable
                $order_product['txn_hash'] = $payment['hash'];
                $order->addProduct($order_product, true);
                $this->addTxnToOrder($order, $payment, [
                    'payment_method' => $method,
                    'card_number' => UtilsService::prepareCardNumber($card['number']),
                    'card_type' => $card['type'] ?? null
                ]);
            }
        }

        // cache token
        self::setCardToken($order->number, $payment['token'] ?? null);

        $order->is_flagged = $payment['is_flagged'];
        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }

        // approve order if txn is approved
        if ($payment['status'] === Txn::STATUS_APPROVED) {
            $order = $this->approveOrder($payment, $payment['payment_provider']);
        }

        // response
        $result = [
            'id'                => null,
            'order_currency'    => $order->currency,
            'order_number'      => $order->number,
            'order_id'          => $order->getIdAttribute(),
            'status'            => self::STATUS_FAIL
        ];

        if ($payment['status'] !== Txn::STATUS_FAILED) {
            $result['id'] = $payment['hash'];
            $result['status'] = self::STATUS_OK;
            $result['redirect_url'] = $payment['redirect_url'] ?? null;
        } else {
            $result['errors'] = $payment['errors'];
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
        $user_agent = $req->header('User-Agent');

        $order = OdinOrder::getById($req->get('order')); // throwable
        $order_main_product = $order->getMainProduct(); // throwable
        $order_main_txn = $order->getTxnByHash($order_main_product['txn_hash']); //throwable
        $main_product = OdinProduct::getBySku($order_main_product['sku_code']); // throwable

        // prepare upsells result
        $upsells = array_map(function($v) {
            $v['status'] = self::STATUS_FAIL;
            return $v;
        }, $upsells);

        $card_token = null;
        $is_upsells_possible = $this->orderService->checkIfUpsellsPossible($order);
        if ($is_upsells_possible) {
            if ($order_main_txn['payment_provider'] === PaymentProviders::BLUESNAP) {
                $is_upsells_possible = !!$order_main_txn['payer_id'];
            } else {
                $card_token = self::getCardToken($order->number);
                $is_upsells_possible = !!$card_token;
            }
        }

        if ($is_upsells_possible) {
            $products = [];
            $upsell_products = [];
            $checkout_price = 0;
            foreach ($upsells as $key => $item) {
                try {
                    $product = $this->productService->getUpsellProductById($main_product, $item['id'], $item['qty'], $order->currency); // throwable
                    $upsell_price = $product->upsellPrices[$item['qty']];
                    $upsell_product = $this->createOrderProduct(
                        $product->upsell_sku,
                        [
                            'currency'  => $upsell_price['code'],
                            'quantity'  => (int)$item['qty'],
                            'value'     => $upsell_price['price'],
                            'value_usd' => $upsell_price['price'] / $upsell_price['exchange_rate']
                        ],
                        [
                            'is_main' => false,
                            'is_plus_one' => ($item['id'] === $main_product->getIdAttribute())
                        ]
                    );
                    $checkout_price += $upsell_price['price'];
                    $products[$product->upsell_sku] = $product;
                    $upsell_products[] = $upsell_product;
                } catch (HttpException $e) {
                    $upsells[$key]['status'] = self::STATUS_FAIL;
                }
            }

            if ($checkout_price >= OdinProduct::MIN_PRICE) {
                // select provider by main txn
                if ($order_main_txn['payment_provider'] === PaymentProviders::APPMAX) {
                    $appmax = new AppmaxService($order_main_txn);
                    $payment = $appmax->payByToken(
                        $card_token,
                        [
                            'street'            => $order->shipping_street,
                            'city'              => $order->shipping_city,
                            'country'           => $order->shipping_country,
                            'state'             => $order->shipping_state,
                            'district'          => $order->shipping_street2,
                            'zip'               => $order->shipping_zip,
                            'email'             => $order->customer_email,
                            'first_name'        => $order->customer_first_name,
                            'last_name'         => $order->customer_last_name,
                            'phone'             => $order->customer_phone,
                            'ip'                => $req->ip()
                        ],
                        array_map(function($item) use($products) {
                            return [
                                'sku'   => $item['sku_code'],
                                'qty'   => $item['quantity'],
                                'name'  => $products[$item['sku_code']]->product_name,
                                'desc'  => $products[$item['sku_code']]->long_name,
                                'image'  => $products[$item['sku_code']]->logo_image,
                                'amount' => $item['price']
                            ];
                        }, $upsell_products),
                        [
                            'amount'        => $checkout_price,
                            'order_id'      => $order->getIdAttribute(),
                            'currency'      => $order->currency,
                            'installments'  => $order->installments,
                            'document_number' => $order->customer_doc_id
                        ]
                    );
                } elseif ($order_main_txn['payment_provider'] === PaymentProviders::EBANX) {
                    $ebanx = new EbanxService();
                    $payment = $ebanx->payByToken(
                        $card_token,
                        [
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
                            'phone'             => $order->customer_phone,
                            'ip'                => $req->ip()
                        ],
                        array_map(function($item) use($products) {
                            return [
                                'sku'   => $item['sku_code'],
                                'qty'   => $item['quantity'],
                                'name'  => $products[$item['sku_code']]->product_name,
                                'desc'  => $products[$item['sku_code']]->description,
                                'amount'    => $item['price'],
                                'is_main'   => false
                            ];
                        }, $upsell_products),
                        [
                            'amount'        => $checkout_price,
                            'currency'      => $order->currency,
                            'number'        => $order->number,
                            'installments'  => $order->installments,
                            'payment_api_id' => $order_main_txn['payment_api_id']
                        ]
                    );
                } elseif ($order_main_txn['payment_provider'] === PaymentProviders::CHECKOUTCOM) {
                    $checkout = new CheckoutDotComService($order_main_txn);
                    $payment = $checkout->payByToken(
                        $card_token,
                        ['payer_id' => $order_main_txn['payer_id'], 'ip' => $order->ip],
                        [
                            'amount'    => $checkout_price,
                            'currency'  => $order->currency,
                            'id'        => $order->getIdAttribute(),
                            'number'    => $order->number,
                            'description' => implode(', ', array_column($products, 'product_name')),
                            // TODO: remove city hardcode
                            'billing_descriptor' => [
                                'name' => $order->billing_descriptor,
                                'city' => 'Msida'
                            ]
                        ]
                    );
                } elseif ($order_main_txn['payment_provider'] === PaymentProviders::MINTE) {
                    $mint = new MinteService();
                    $payment = $mint->payByToken(
                        $card_token,
                        [
                            'street'        => $order->shipping_street,
                            'city'          => $order->shipping_city,
                            'country'       => $order->shipping_country,
                            'state'         => $order->shipping_state,
                            'zip'           => $order->shipping_zip,
                            'email'         => $order->customer_email,
                            'first_name'    => $order->customer_first_name,
                            'last_name'     => $order->customer_last_name,
                            'phone'         => $order->customer_phone,
                            'ip'            => $req->ip()
                        ],
                        [
                            'amount'    => $checkout_price,
                            'currency'  => $order->currency,
                            'order_id'  => $order->getIdAttribute(),
                            'descriptor'    => $order->billing_descriptor,
                            'order_number'  => $order->number,
                            'user_agent'    => $user_agent,
                            'payment_api_id' => $order_main_txn['payment_api_id']
                        ]
                    );
                } elseif ($order_main_txn['payment_provider'] === PaymentProviders::BLUESNAP) {
                    $bluesnap = new BluesnapService();
                    $payment = $bluesnap->payByVaultedShopperId(
                        $order_main_txn['payer_id'],
                        [
                            'amount'    => $checkout_price,
                            'currency'  => $order->currency,
                            'billing_descriptor' => $order->billing_descriptor,
                            'payment_api_id' => $order_main_txn['payment_api_id']
                        ]
                    );
                }

                // update order
                $upsells = array_map(function($v) use ($payment) {
                    if (in_array($payment['status'], [Txn::STATUS_CAPTURED, Txn::STATUS_APPROVED])) {
                        $v['status'] = self::STATUS_OK;
                    } else {
                        $v['status'] = self::STATUS_FAIL;
                        $v['errors'] = $payment['errors'];
                    }
                    return $v;
                }, $upsells);

                // add upsell products
                foreach ($upsell_products as $item) {
                    $item['txn_hash'] = $payment['hash'];
                    $order->addProduct($item);
                }

                $this->addTxnToOrder($order, $payment, $order_main_txn);

                if ($order->status === OdinOrder::STATUS_PAID) {
                    $order->status = OdinOrder::STATUS_HALFPAID;
                }

                $checkout_price += $order_main_product['price'] + $order_main_product['warranty_price'];
                $order->total_price = CurrencyService::roundValueByCurrencyRules($checkout_price, $order->currency);
                $order->total_price_usd = CurrencyService::roundValueByCurrencyRules($order->total_price / $order->exchange_rate, Currency::DEF_CUR);
                $order->is_invoice_sent = false;

                if (!$order->save()) {
                    $validator = $order->validate();
                    if ($validator->fails()) {
                        throw new OrderUpdateException(json_encode($validator->errors()->all()));
                    }
                }

                // approve order if txn is approved
                if ($payment['status'] === Txn::STATUS_APPROVED) {
                    $order = $this->approveOrder($payment, $payment['payment_provider']);
                }
            }
        }

        return [
            'order_currency'    => $order->currency,
            'order_number'      => $order->number,
            'order_id'          => $order->getIdAttribute(),
            'id'                => $order_main_product['txn_hash'],
            'status'            => $order_main_txn['status'] !== Txn::STATUS_FAILED ? self::STATUS_OK : self::STATUS_FAIL,
            'upsells'           => $upsells
        ];
    }

    /**
     * Invokes fallback provider
     * @param  OdinOrder $order
     * @param  array     $card
     * @param  array     $details
     * [
     *   'provider'   => string,
     *   'product_id' => ?string,
     *   'payment_api_id' => ?string,
     *   'method'     => string,
     *   'useragent'  => ?string
     * ]
     * @return array
     */
    public function fallbackOrder(OdinOrder &$order, array $card, array $details): array
    {
        $is_fallback_available = self::checkIsFallbackAvailable(
            $details['provider'],
            $order->ipqualityscore,
            ['installments' => $order->installments]
        );
        $fallback_provider = self::getProviderByCountryAndMethod($order->shipping_country, $details['method'], false);

        $result = ['status' => false, 'payment' => []];

        if (!$is_fallback_available || !$fallback_provider) {
            logger()->info("Fallback is not available [{$order->number}] provider {$fallback_provider}");
            return $result;
        }

        logger()->info("Fallback [{$order->number}] provider {$fallback_provider}");

        switch ($fallback_provider):
            case PaymentProviders::BLUESNAP:
                $order = $this->checkOrderCurrency($order, PaymentProviders::BLUESNAP);
                $bluesnap = new BluesnapService();
                $result['status']  = true;
                $result['payment'] = $bluesnap->payByCard(
                    $card,
                    [
                        'street'            => $order->shipping_street,
                        'city'              => $order->shipping_city,
                        'country'           => $order->shipping_country,
                        'state'             => $order->shipping_state,
                        'district'          => $order->shipping_street2,
                        'zip'               => $order->shipping_zip,
                        'email'             => $order->customer_email,
                        'first_name'        => $order->customer_first_name,
                        'last_name'         => $order->customer_last_name,
                        'phone'             => $order->customer_phone
                    ],
                    [
                        'amount'        => $order->total_price,
                        'currency'      => $order->currency,
                        'number'        => $order->number,
                        'billing_descriptor'   => $order->billing_descriptor,
                        'payment_api_id' => $details['payment_api_id'] ?? null
                    ]
                );
                break;
            case PaymentProviders::MINTE:
                $order = $this->checkOrderCurrency($order, PaymentProviders::MINTE);
                $mint = new MinteService();
                $result['status']  = true;
                $result['payment'] = $mint->payByCard(
                    $card,
                    [
                        'street'        => $order->shipping_street,
                        'city'          => $order->shipping_city,
                        'country'       => $order->shipping_country,
                        'state'         => $order->shipping_state,
                        'zip'           => $order->shipping_zip,
                        'email'         => $order->customer_email,
                        'first_name'    => $order->customer_first_name,
                        'last_name'     => $order->customer_last_name,
                        'phone'         => $order->customer_phone,
                        'ip'            => $order->ip
                    ],
                    [
                        '3ds'       => false,
                        'amount'    => $order->total_price,
                        'currency'  => $order->currency,
                        'order_id'  => $order->getIdAttribute(),
                        'order_number'  => $order->number,
                        'descriptor'    => $order->billing_descriptor,
                        'product_id'    => $details['product_id'] ?? null,
                        'user_agent'    => $details['useragent'] ?? null,
                        'payment_api_id' => $details['payment_api_id'] ?? null
                    ]
                );
                break;
            default:
                logger()->info("Fallback [{$order->number}] provider not found");
        endswitch;

        return $result;
    }

    /**
     * Approves order
     * @param array $data ['hash'=>string,'number'=>?string,'fee_usd'=>?float,'value'=>?float,'status'=>string]
     * @return OdinOrder|null
     */
    public function approveOrder(array $data, ?string $provider = null): ?OdinOrder
    {
        $order = null;
        if (!empty($data['number'])) {
            $order = OdinOrder::getByNumber($data['number']); // throwable
        } elseif (!empty($data['hash']) && $provider) {
            $order = OdinOrder::getByTxnHash($data['hash'], $provider); // throwable
        } else {
            logger()->error('Order approve failed', $data);
            return $order;
        }

        // check webhook reply
        if (!in_array($order->status, [OdinOrder::STATUS_NEW, OdinOrder::STATUS_HALFPAID])) {
            logger()->info("Webhook ignored, order [{$order->number}] status [{$order->status}]", ['data' => $data]);
            return $order;
        }

        $txn = $order->getTxnByHash($data['hash'], false);
        if ($txn) {
            if (isset($data['fee_usd'])) {
                $txn['fee_usd'] = $data['fee_usd'];
            }
            if (isset($data['value'])) {
                $txn['value'] = $data['value'];
            }
            $txn['status']  = $data['status'];
            $order->addTxn($txn);
        }

        if ($txn && $txn['status'] === Txn::STATUS_APPROVED) {
            $products = $order->getProductsByTxnHash($data['hash']);
            foreach ($products as $product) {
                $product['is_paid'] = true;
                if ($product['is_main']) {
                    $order->is_flagged = false;
                }
                $order->addProduct($product);
            }

            $currency = CurrencyService::getCurrency($order->currency);

            $total = collect($order->txns)->reduce(function ($carry, $item) {
                if ($item['status'] === Txn::STATUS_APPROVED) {
                    $carry['value'] += $item['value'];
                }
                return $carry;
            }, ['value' => 0]);

            $order->total_paid      = CurrencyService::roundValueByCurrencyRules($total['value'], $currency->code);
            $order->total_paid_usd  = CurrencyService::roundValueByCurrencyRules($total['value'] / $currency->usd_rate, Currency::DEF_CUR);
            //$order->txns_fee_usd    = CurrencyService::roundValueByCurrencyRules($total['fee_usd'] / $currency->usd_rate, Currency::DEF_CUR);
            $order->txns_fee_usd = 0;

            $price_paid_diff    = floor($order->total_paid * 100 - $order->total_price * 100) / 100;
            $order->status      = $price_paid_diff >= 0 ? OdinOrder::STATUS_PAID : OdinOrder::STATUS_HALFPAID;
        }

        logger()->info("Approve order saving", ['hash' => $data['hash']]);
        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }

        return $order;
    }

    /**
     * Returns supported currency
     * @param  string $country
     * @param  string $currency
     * @param  string $prv default=minte
     * @return string
     */
    public function checkCurrency(string $country, string $currency, string $prv = PaymentProviders::MINTE): string
    {
        switch ($prv):
            case PaymentProviders::EBANX:
                return EbanxService::getCurrencyByCountry($country, $currency);
            case PaymentProviders::MINTE:
                return MinteService::getCurrencyByCountry($country, $currency);
            default:
                return $currency;
        endswitch;
    }

    /**
     * Checks(Changes) order currency
     * @param  OdinOrder   $order
     * @param  string      $prv
     * @return OdinOrder
     */
    public function checkOrderCurrency(OdinOrder $order, string $provider = PaymentProviders::MINTE): OdinOrder
    {
        $order_product = $order->getMainProduct(); // throwable

        $product = null;
        if ($order_product['price_set']) {
            $product = OdinProduct::getByCopId($order_product['price_set']);
        }
        if (!$product) {
            $product = OdinProduct::getBySku($order_product['sku_code']); // throwable
        }

        $price = $this->getLocalizedPrice($product, $order_product['quantity'], $order->shipping_country, $provider); // throwable

        if ($order->currency === $price['currency']) {
            return $order;
        }

        logger()->info("Fallback [{$order->number}] change currency {$order->currency} -> USD");

        $order_product = $this->createOrderProduct($order_product['sku_code'], $price, ['is_warranty' => !!$order_product['warranty_price']]);

        $order->currency        = $price['currency'];
        $order->exchange_rate   = $price['usd_rate'];
        $order->total_price     = $order_product['total_price'];
        $order->total_price_usd = $order_product['total_price_usd'];
        $order->addProduct($order_product, true);

        return $order;
    }

    /**
     * Mint-e 3ds redirect
     * @param  PaymentCardMinte3dsRequest $req
     * @param string $order_id
     * @return bool
     */
    public function minte3ds(PaymentCardMinte3dsRequest $req, string $order_id): bool
    {
        $errcode    = $req->input('errorcode');
        $errmsg     = $req->input('errormessage');
        $sign       = $req->input('signature');
        $txn_hash   = $req->input('transid');
        $txn_status = $req->input('status');
        $txn_ts     = $req->input('timestamp');

        $order = OdinOrder::getById($order_id); // throwable
        $order_txn = $order->getTxnByHash($txn_hash); // throwable

        $mint = new MinteService();
        $reply = $mint->captureMinte3ds([
            'errcode'   => $errcode,
            'errmsg'    => $errmsg,
            'sign'      => $sign,
            'hash'      => $txn_hash,
            'status'    => $txn_status,
            'timestamp' => $txn_ts,
            'order_id'  => $order_id,
            'payment_api_id' => $order_txn['payment_api_id']
        ]);

        if (!$reply['status']) {
            logger()->error('Mint-e unauthorized redirect', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $result = false;
        if ($reply['txn']['status'] === Txn::STATUS_APPROVED) {
            $this->approveOrder($reply['txn'], PaymentProviders::MINTE);
            $result = true;
        } else {
            $order = $this->rejectTxn($reply['txn'], PaymentProviders::MINTE);

            PaymentService::cacheErrors(array_merge($reply['txn'], ['number' => $order->number]));

            $cardtk = self::getCardToken($order->number, false);

            logger()->info("Pre-Fallback [{$order->number}]", ['cardtk' => !!$cardtk, 'fallback' => !empty($reply['txn']['fallback'])]);

            if (!empty($reply['txn']['fallback']) && $cardtk) {
                $cardjs = MinteService::decrypt($cardtk, $order_id);

                $reply = $this->fallbackOrder($order, json_decode($cardjs, true), [
                    'provider'   => PaymentProviders::MINTE,
                    'payment_api_id' => $order_txn['payment_api_id'],
                    'method'     => $order_txn['payment_method']
                ]);

                if ($reply['status']) {
                    $result = true;

                    $order_product = $order->getMainProduct(); // throwable
                    $order_product['txn_hash'] = $reply['payment']['hash'];
                    $order->addProduct($order_product, true);
                    $this->addTxnToOrder($order, $reply['payment'], $order_txn);
                    $order->is_flagged = $reply['payment']['is_flagged'];

                    logger()->info("Minte 3ds order saving", ['hash' => $reply['payment']['hash']]);
                    if (!$order->save()) {
                        $validator = $order->validate();
                        if ($validator->fails()) {
                            throw new OrderUpdateException(json_encode($validator->errors()->all()));
                        }
                    }

                    if ($reply['payment']['status'] === Txn::STATUS_FAILED) {
                        PaymentService::cacheErrors(array_merge($reply['payment'], ['number' => $order->number]));
                        $reslt = false;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Approves order by ebanx hashes
     * @param  array  $hashes
     * @return void
     */
    public function ebanxNotification(array $hashes): void
    {
        $ebanx = new EbanxService();
        foreach ($hashes as $hash) {
            $order = OdinOrder::getByTxnHash($hash, PaymentProviders::EBANX, false);

            if (!$order) {
                logger()->warning('Order not found by hash', ['hash' => $hash]);
                continue;
            }

            $txn = $order->getTxnByHash($hash, false);

            $payment = $ebanx->requestStatusByHash($hash, $txn ?? []);

            if (!empty($payment['number'])) {
                $this->approveOrder($payment, PaymentProviders::EBANX);
            } else {
                logger()->warning('Ebanx payment not found', ['hash' => $hash]);
            }
        }
    }

    /**
     * Handles Appmax webhook
     * @param string $event
     * @param array  $data
     * @return void
     */
    public function appmaxWebhook(string $event, array $data): void
    {
        $order = OdinOrder::getByTxnHash($data['id'], PaymentProviders::APPMAX); //throwable
        $txn = $order->getTxnByHash((string)$data['id'], false);

        $appmax = new AppmaxService($txn);
        $reply = $appmax->validateWebhook($event, $data);

        if ($reply['status']) {
            $this->approveOrder($reply['txn'], PaymentProviders::APPMAX);
        }
    }

    /**
     * Reject txn
     * @param array $data
     * @return OdinOrder|null
     */
    public function rejectTxn(array $data, ?string $provider = null): ?OdinOrder
    {
        $order = null;
        if (!empty($data['number'])) {
            $order = OdinOrder::getByNumber($data['number']); // throwable
        } elseif (!empty($data['hash']) && $provider) {
            $order = OdinOrder::getByTxnHash($data['hash'], $provider); // throwable
        } else {
            logger()->error('Order txn reject failed', $data);
            return $order;
        }

        $txn = $order->getTxnByHash($data['hash'], false);
        if ($txn) {
            $txn['status'] = $data['status'];
            $order->addTxn($txn);
        }

        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }
        return $order;
    }

    /**
     * Caches webhook errors
     * @param array  $data
     * @return void
     */
    public static function cacheErrors(array $data = []): void
    {
        $order_number = $data['number'] ?? null;
        $errors = $data['errors'] ?? null;
        if ($order_number && !empty($errors)) {
            $dt = (new \DateTime())->add(new \DateInterval("PT" . self::CACHE_ERRORS_TTL_MIN . "M"));
            Cache::put(self::CACHE_ERRORS_PREFIX . $order_number, \json_encode($errors), $dt);
        }
    }

    /**
     * Checks payment to fraud
     * @param  ?array   $ipqs
     * @param  bool     $thowable
     * @return void
     * @throws PaymentException
     */
    public static function fraudCheck(?array $ipqs, string $prv = PaymentProviders::MINTE, bool $throwable = true): void
    {
        if (!empty($ipqs) && \App::environment() === 'production') {
            $fraud_chance = $ipqs['fraud_chance'] ?? PaymentService::FRAUD_CHANCE_MAX;
            $is_bot = $ipqs['bot_status'] ?? false;
            $is_valid_email = !empty($ipqs['transaction_details']) ? $ipqs['transaction_details']['valid_billing_email'] ?? null : null;
            $refuse_limit = PaymentProviders::$list[$prv]['fraud_setting']['refuse_limit'];
            if ($fraud_chance > $refuse_limit || $is_bot || $is_valid_email === false) {
                throw new PaymentException('Payment is refused', 'card.error.refused');
            }
        }
    }

    /**
     * Get Order errors
     * @param string $order_id
     * @return array
     */
    public static function getOrderErrors(string $order_id): array
    {
        $order = OdinOrder::getById($order_id);
        $cache_reply = Cache::get(self::CACHE_ERRORS_PREFIX . $order->number);
        return $cache_reply ? json_decode($cache_reply, true) : [];
    }

    /**
     * Returns payment methods array by country
     * Results example:
     * $result = [
     *   PaymentProviders::CHECKOUTCOM => [
     *     PaymentMethods::VISA => [
     *       'name' => 'VISA',
     *       'logo' => 'https://static-backend.saratrkr.com/image_assets/visa-curved-128px.png',
     *       '3ds' => true
     *     ]
     *   ]
     * ];
     * @param string $country
     * @param bool   $is_main default=true
     * @return boolean
     */
    public static function getPaymentMethodsByCountry(string $country, bool $is_main = true)
    {
        $country = strtolower($country);
        $result = [];
        foreach (PaymentProviders::$list as $providerId => $provider) {
            if (PaymentProviders::isActive($providerId, $is_main)) {
                $result[$providerId] = [];

                //check every method of provider
                foreach ($provider['methods'][$is_main ? 'main' : 'fallback'] as $methodId => $method) {
                    if (PaymentMethods::$list[$methodId]['is_active']) {
                        //check 3DS settings
                        if (!empty($method['+3ds']) && static::checkIfMethodInCountries($country, $method['+3ds'])) {
                            $result[$providerId][$methodId] = ['3ds' => true];
                        } elseif (!empty($method['-3ds']) && static::checkIfMethodInCountries($country, $method['-3ds'])) {
                            $result[$providerId][$methodId] = ['3ds' => false];
                        }

                        //check if country is excluded
                        if (!empty($method['excl']) && static::checkIfMethodInCountries($country, $method['excl'])) {
                            unset($result[$providerId][$methodId]);
                        }
                    }
                }
                if ($result[$providerId]) {
                    foreach ($result[$providerId] as $methodId => &$methodData) {
                        $method             = PaymentMethods::$list[$methodId];
                        $methodData['name'] = $method['name'];
                        $methodData['logo'] = \Utils::getCdnUrl(true).$method['logo'];
                        if (isset($provider['extra_fields']) && isset($provider['extra_fields'][$country])) {
                            $methodData['extra_fields'] = $provider['extra_fields'][$country];
                        }
                    }
                } else {
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
     * @param   bool   $is_main
     * @param   string $pref default=checkoutcom
     * @param   array  $excl default=[]
     * @return  string|null
     */
    public static function getProviderByCountryAndMethod(string $country, string $method, bool $is_main = true, string $pref = PaymentProviders::MINTE, array $excl = []): ?string
    {
        $providers = self::getPaymentMethodsByCountry($country, $is_main);

        if (!EbanxService::isCountrySupported($country)) {
            $excl[] = PaymentProviders::EBANX;
        }

        $available_providers = [];
        foreach ($providers as $prv => $methods) {
            if (isset($methods[$method]) && !in_array($prv, $excl)) {
                $available_providers[] = $prv;
            }
        }
        return in_array($pref, $available_providers) ? $pref : array_pop($available_providers);
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
            return Cache::pull(self::CACHE_TOKEN_PREFIX . $order_number);
        }
        return Cache::get(self::CACHE_TOKEN_PREFIX . $order_number);
    }

    /**
     * Returns CheckoutDotComService by order number
     * @param  string $number
     * @param  string $hash
     * @return CheckoutDotComService
     */
    public static function getCheckoutService(string $number, string $hash): CheckoutDotComService
    {
        $order = OdinOrder::getByNumber($number); //throwable
        $txn = $order->getTxnByHash($hash, false);
        return new CheckoutDotComService($txn ?? []);
    }

    /**
     * Returns CheckoutDotComService by order number
     * @param  string $number
     * @param  string $hash
     * @return CheckoutDotComService
     */
    public static function getAppmaxService(string $hash): AppmaxService
    {
        $order = OdinOrder::getByNumber($number); //throwable
        $txn = $order->getTxnByHash($hash, false);
        return new CheckoutDotComService($txn ?? []);
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
            $dt = (new \DateTime())->add(new \DateInterval("PT" . self::CACHE_TOKEN_TTL_MIN . "M"));
            Cache::put(self::CACHE_TOKEN_PREFIX . $order_number, $token, $dt);
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
     * @param  string $method
     * @param  string $country
     * @param  string $prv default=minte
     * @param  array  $ipqs
     * @return bool
     */
    private static function checkIs3dsNeeded(string $method, string $country, string $prv = PaymentProviders::MINTE, array $ipqs = []): bool
    {
        $result = true;
        $setting = PaymentProviders::$list[$prv]['methods']['main'][$method] ?? [];
        $fraud_chance = $ipqs['fraud_chance'] ?? PaymentService::FRAUD_CHANCE_MAX;
        $fraud_chance_limit = PaymentProviders::$list[$prv]['fraud_setting']['3ds_limit'];

        if ($fraud_chance < $fraud_chance_limit) {
            if (in_array($country, $setting['+3ds'] ?? []) ) {
                $result = true;
            } else if (in_array('*', $setting['-3ds'] ?? []) || in_array($country, $setting['-3ds'] ?? [])) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Check if fallback provider available
     * @param  string $prv
     * @param  array|null  $ipqs
     * @param  array|null  $details
     * @return bool
     */
    private static function checkIsFallbackAvailable(string $prv, ?array $ipqs = [], ?array $details = []): bool
    {
        $fraud_chance = $ipqs['fraud_chance'] ?? PaymentService::FRAUD_CHANCE_MAX;
        $fallback_limit = PaymentProviders::$list[$prv]['fraud_setting']['fallback_limit'];

        $result = $fraud_chance < $fallback_limit;

        if ($prv === PaymentProviders::EBANX) {
            $result = !empty($details['installments']) && $details['installments'] <= EbanxService::INSTALLMENTS_MIN;
        }

        return $result;
    }


    public function test(Request $req)
    {
        ['sku' => $sku, 'qty' => $qty] = $req->get('product');
        $is_warranty = (bool)$req->input('product.is_warranty_checked', false);
        $page_checkout = $req->input('page_checkout', $req->header('Referer'));
        $user_agent = $req->header('User-Agent');
        $ipqs = $req->input('ipqs', null);
        $card = $req->get('card');
        $order_id = $req->get('order');
        $installments = (int)$req->input('card.installments', 0);
        $contact = array_merge(
            $req->get('contact'),
            $req->get('address'),
            ['ip' => $req->ip(), 'email' => strtolower($req->input('contact.email'))]
        );
        $method = PaymentMethodMapper::toMethod($card['number']);

        // find order for update
        $order = null;
        if (!empty($order_id)) {
            $order = OdinOrder::findExistedOrderForPay($order_id, $req->get('product'));
        }

        $product = null;
        if ($req->get('cop_id')) {
            $product = OdinProduct::getByCopId($req->get('cop_id'));
        }
        if (!$product) {
            $product = OdinProduct::getBySku($sku); // throwable
        }

        // select provider by country
        $provider = PaymentProviders::APPMAX;

        self::fraudCheck($ipqs, $provider); // throwable

        $this->addCustomer($contact); // throwable

        if (empty($order)) {;
            $price = $this->getLocalizedPrice($product, (int)$qty, $contact['country'], $provider); // throwable

            $order_product = $this->createOrderProduct($sku, $price, ['is_warranty' => $is_warranty]);

            $params = !empty($page_checkout) ? UtilsService::getParamsFromUrl($page_checkout) : null;
            $affId = AffiliateService::getAttributeByPriority($params['aff_id'] ?? null, $params['affid'] ?? null);
            $offerId = AffiliateService::getAttributeByPriority($params['offer_id'] ?? null, $params['offerid'] ?? null);
            $validTxid = AffiliateService::getValidTxid($params['txid'] ?? null);

            $order = $this->addOrder([
                'billing_descriptor'    => $product->getPaymentBillingDescriptor($contact['country']),
                'currency'              => $price['currency'],
                'exchange_rate'         => $price['usd_rate'],
                'total_paid'            => 0,
                'total_paid_usd'        => 0,
                'total_price'           => $order_product['total_price'],
                'total_price_usd'       => $order_product['total_price_usd'],
                'txns_fee_usd'          => 0,
                'installments'          => $installments,
                'is_reduced'            => false,
                'is_invoice_sent'       => false,
                'is_survey_sent'        => false,
                'is_flagged'            => false,
                'is_refunding'          => false,
                'is_refunded'           => false,
                'is_qc_passed'          => false,
                'customer_email'        => $contact['email'],
                'customer_first_name'   => $contact['first_name'],
                'customer_last_name'    => $contact['last_name'],
                'customer_phone'        => $contact['phone']['country_code'] . UtilsService::preparePhone($contact['phone']['number']),
                'customer_doc_id'       => $contact['document_number'] ?? null,
                'ip'                    => $contact['ip'],
                'language'              => app()->getLocale(),
                'txns'                  => [],
                'shipping_country'      => $contact['country'],
                'shipping_zip'          => $contact['zip'],
                'shipping_state'        => $contact['state'],
                'shipping_city'         => $contact['city'],
                'shipping_street'       => $contact['street'],
                'shipping_street2'      => $contact['district'] ?? null,
                'shop_currency'         => CurrencyService::getCurrency()->code,
                'warehouse_id'          => $product->warehouse_id,
                'products'              => [$order_product],
                'page_checkout'         => $page_checkout,
                'params'                => $params,
                'offer'                 => $offerId,
                'affiliate'             => $affId,
                'txid'                  => $validTxid,
                'ipqualityscore'        => $ipqs
            ]);
        } else {
            $order_product = $order->getMainProduct(); // throwable
            $order->customer_email      = $contact['email'];
            $order->customer_first_name = $contact['first_name'];
            $order->customer_last_name  = $contact['last_name'];
            $order->customer_phone      = $contact['phone']['country_code'] . UtilsService::preparePhone($contact['phone']['number']);
            $order->customer_doc_id     = $contact['document_number'] ?? null;
            $order->shipping_country    = $contact['country'];
            $order->shipping_zip        = $contact['zip'];
            $order->shipping_state      = $contact['state'];
            $order->shipping_city       = $contact['city'];
            $order->shipping_street     = $contact['street'];
            $order->shipping_street2    = $contact['district'] ?? null;
            $order->installments        = $installments;
        }

        $appmax = new AppmaxService(['product_id' => $product->getIdAttribute()]);
        $payment = $appmax->payByCard(
            $card,
            $contact,
            [
                [
                    'sku'   => $sku,
                    'qty'   => $qty,
                    'name'  => $product->product_name,
                    'desc'  => $product->long_name,
                    'image' => $product->logo_image,
                    'amount'    => $order->total_price,
                ]
            ],
            [
                'amount'        => $order->total_price,
                'order_id'      => $order->getIdAttribute(),
                'currency'      => $order->currency,
                'installments'  => $installments,
                'document_number' => $order->customer_doc_id
            ]
        );

        $this->addTxnToOrder($order, $payment, [
            'payment_method' => $method,
            'card_number' => UtilsService::prepareCardNumber($card['number']),
            'card_type' => $card['type'] ?? null
        ]);

        $order_product['txn_hash'] = $payment['hash'];
        $order->addProduct($order_product, true);

        // cache token
        self::setCardToken($order->number, $payment['token'] ?? null);

        $order->is_flagged = $payment['is_flagged'];
        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }

        // approve order if txn is approved
        if ($payment['status'] === Txn::STATUS_APPROVED) {
            $order = $this->approveOrder($payment, $payment['payment_provider']);
        }

        // response
        $result = [
            'id'                => null,
            'order_currency'    => $order->currency,
            'order_number'      => $order->number,
            'order_id'          => $order->getIdAttribute(),
            'status'            => self::STATUS_FAIL
        ];

        if ($payment['status'] !== Txn::STATUS_FAILED) {
            $result['id'] = $payment['hash'];
            $result['status'] = self::STATUS_OK;
            $result['redirect_url'] = $payment['redirect_url'] ?? null;
        } else {
            $result['errors'] = $payment['errors'];
        }

        return $result;
    }

}
