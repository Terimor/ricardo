<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use Illuminate\Http\Request;
use App\Exceptions\CustomerUpdateException;
use App\Exceptions\InvalidParamsException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\OrderUpdateException;
use App\Exceptions\PaymentException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\TxnNotFoundException;
use App\Models\Txn;
use App\Models\Currency;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Services\CurrencyService;
use App\Services\CustomerService;
use App\Services\CheckoutDotComService;
use App\Services\EbanxService;
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

    const FRAUD_CHANCE_LIMIT    = 90;
    const FRAUD_CHANCE_MAX      = 100;

    const SUCCESS_PATH  =   '/checkout';
    const FAILURE_PATH  =   '/checkout';

    const STATUS_OK     = 'ok';
    const STATUS_FAIL   = 'fail';

    const CACHE_TOKEN_PREFIX    = 'CardToken';
    const CACHE_ERRORS_PREFIX   = 'PayErrors';
    const CACHE_TOKEN_TTL_MIN   = 15;
    const CACHE_ERRORS_TTL_MIN  = 1;

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
     * @param  string       $payment_method
     * @param  string|null  $card_type
     * @return void
     */
    private function addTxnToOrder(OdinOrder &$order, array $data, string $payment_method, ?string $card_type): void
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
            'card_type'         => $card_type,
            'is_charged_back'   => false,
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

        $price_usd = $localized_product->prices[$qty]['value'] / $currency->usd_rate;

        return [
            'currency'          => $currency->code,
            'price_set'         => $product->prices['price_set'] ?? '',
            'quantity'          => $qty,
            'usd_rate'          => $currency->usd_rate,
            'value'             => $localized_product->prices[$qty]['value'],
            'value_usd'         => $price_usd,
            'warranty_value'    => $localized_product->prices[$qty]['warranty_price'],
            'warranty_value_usd'    => ($product->warranty_percent ?? 0) * $price_usd / 100
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
            'price'                 => $price['value'],
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
            'total_price'           => $price['value'],
            'total_price_usd'       => CurrencyService::roundValueByCurrencyRules($price['value_usd'], Currency::DEF_CUR)
        ];

        $is_warranty = $details['is_warranty'] ?? false;
        if ($is_warranty) {
            $order_product['warranty_price']        = $price['warranty_value'];
            $order_product['warranty_price_usd']    = CurrencyService::roundValueByCurrencyRules($price['warranty_value_usd'], Currency::DEF_CUR);
            $order_product['total_price']           = CurrencyService::roundValueByCurrencyRules($price['value'] + $price['warranty_value'], $price['currency']);
            $order_product['total_price_usd']       = CurrencyService::roundValueByCurrencyRules($order_product['total_price'] / $price['usd_rate'], Currency::DEF_CUR);
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
        $contact = array_merge($req->get('contact'), $req->get('address'), ['ip' => $req->ip()]);
        $page_checkout = $req->input('page_checkout', $req->header('Referer'));
        $ipqs = $req->input('ipqs', null);
        $cur = $req->get('cur');
        $card = $req->get('card');
        $order_id = $req->get('order');
        $installments = (int)$req->input('card.installments', 0);
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
                "Provider not found",
                ['country' => $contact['country'], 'method' => $method, 'card' => substr_replace($card['number'], '********', 4, 8)]
            );
            $provider = PaymentProviders::CHECKOUTCOM;
        } else if ($provider === PaymentProviders::EBANX) {
            // check if ebanx supports currency, otherwise switch to default currency
            $product->currency = EbanxService::getCurrencyByCountry($contact['country'], $cur);
        }

        $this->addCustomer($contact); // throwable

        if (empty($order)) {
            $price = $this->getLocalizedPrice($product, (int)$qty); // throwable

            $order_product = $this->createOrderProduct($sku, $price, ['is_warranty' => $is_warranty]);

            $params = !empty($page_checkout) ? \Utils::getParamsFromUrl($page_checkout) : null;
            $affId = AffiliateService::getAttributeByPriority($params['aff_id'] ?? null, $params['affid'] ?? null);
            $offerId = AffiliateService::getAttributeByPriority($params['offer_id'] ?? null, $params['offerid'] ?? null);
            $validTxid = AffiliateService::getValidTxid($params['txid'] ?? null);

            $order = $this->addOrder([
                'currency'              => $price['currency'],
                'exchange_rate'         => $price['usd_rate'],
                'total_paid'            => 0,
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
                'customer_phone'        => $contact['phone']['country_code'] . $contact['phone']['number'],
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
                'shop_currency'         => $price['currency'],
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
            $order->customer_phone      = $contact['phone']['country_code'] . $contact['phone']['number'];
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
        if ($provider === PaymentProviders::EBANX) {
            $ebanxService = new EbanxService();
            $payment = $ebanxService->payByCard(
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
                    'installments'  => $installments
                ]
            );
        } else {
            $checkoutService = new CheckoutDotComService();
            $payment = $checkoutService->payByCard($card, $contact, [
                'amount'    => $order->total_price,
                'currency'  => $order->currency,
                'ip'        => $order->ip,
                'id'        => $order->getIdAttribute(),
                'number'    => $order->number,
                '3ds'       => self::checkIs3dsNeeded($method, $contact['country'], $ipqs),
                'description'   => $product->product_name,
                // TODO: remove city hardcode
                'billing_descriptor'   => ['name' => $product->billing_descriptor, 'city' => 'Msida']
            ]);
            if ($payment['status'] !== Txn::STATUS_FAILED) {
                $payment['token'] = $checkoutService->requestToken($card, $contact);
            }
        }

        // cache token
        self::setCardToken($order->number, $payment['token']);

        // add Txn, update OdinOrder
        if (!empty($payment['hash'])) {
            $order_product['txn_hash'] = $payment['hash'];
            $this->addTxnToOrder($order, $payment, $method, $card['type'] ?? null);
            $order->addProduct($order_product, true);
            $order->is_flagged = $payment['is_flagged'];
            if (!$order->save()) {
                $validator = $order->validate();
                if ($validator->fails()) {
                    throw new OrderUpdateException(json_encode($validator->errors()->all()));
                }
            }
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
                if ($order_main_txn['payment_provider'] === PaymentProviders::EBANX) {
                    $ebanxService = new EbanxService();
                    $payment = $ebanxService->payByToken(
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
                            'installments'  => $order->installments
                        ]
                    );
                } else {
                    $checkoutService = new CheckoutDotComService();
                    $payment = $checkoutService->payByToken(
                        $card_token,
                        ['payer_id' => $order_main_txn['payer_id']],
                        [
                            'amount'    => $checkout_price,
                            'currency'  => $order->currency,
                            'ip'        => $order->ip,
                            'id'        => $order->getIdAttribute(),
                            'number'    => $order->number,
                            'description'   => implode(', ', array_column($products, 'product_name')),
                            // TODO: remove city hardcode
                            'billing_descriptor'   => ['name' => $main_product->billing_descriptor, 'city' => 'Msida']
                        ]
                    );
                }

                // update order if transaction is passed
                if (!empty($payment['hash'])) {

                    $upsells = array_map(function($v) use ($payment) {
                        if ($payment['status'] !== Txn::STATUS_FAILED) {
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

                    $this->addTxnToOrder($order, $payment, $order_main_txn['payment_method'], $order_main_txn['card_type']);

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
        if (!$order || !in_array($order->status, [OdinOrder::STATUS_NEW, OdinOrder::STATUS_HALFPAID])) {
            return;
        }

        $txn = $order->getTxnByHash($data['hash'], false);
        if ($txn) {
            $txn['fee']     = $data['fee'];
            $txn['status']  = $data['status'];
            $txn['value']   = $data['value'];
            $order->addTxn($txn);
        }

        if ($txn && $txn['status'] === Txn::STATUS_APPROVED) {
            $products = $order->getProductsByTxnHash($data['hash']);
            foreach ($products as $product) {
                $product['is_paid'] = true;
                $order->addProduct($product);
            }

            $currency = CurrencyService::getCurrency($order->currency);

            $total = collect($order->txns)->reduce(function ($carry, $item) {
                if ($item['status'] === Txn::STATUS_APPROVED) {
                    $carry['value'] += $item['value'];
                    $carry['fee']   += $item['fee'];
                }
                return $carry;
            }, ['value' => 0, 'fee' => 0]);

            $order->total_paid      = CurrencyService::roundValueByCurrencyRules($total['value'], $currency->code);
            $order->total_paid_usd  = CurrencyService::roundValueByCurrencyRules($total['value'] / $currency->usd_rate, Currency::DEF_CUR);
            $order->txns_fee_usd    = CurrencyService::roundValueByCurrencyRules($total['fee'] / $currency->usd_rate, Currency::DEF_CUR);

            $price_paid_diff    = floor($order->total_paid * 100 - $order->total_price * 100) / 100;
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
     * Reject txn
     * @param array $data
     * @return void
     */
    public function rejectTxn(array $data): void
    {
        $order = OdinOrder::getByNumber($data['order_number']); // throwable

        $txn = $order->getTxnByHash($data['txn_hash'], false);
        if ($txn) {
            $txn['status'] = $data['txn_status'];
            $order->addTxn($txn);
        }

        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }
    }

    /**
     * Caches webhook errors
     * @param array  $data
     * @return void
     */
    public static function cacheErrors(array $data = []): void
    {
        $order_number = $data['order_number'] ?? null;
        $errors = $data['errors'] ?? null;
        if ($order_number && !empty($errors)) {
            $dt = (new \DateTime())->add(new \DateInterval("PT" . self::CACHE_ERRORS_TTL_MIN . "M"));
            Cache::put(self::CACHE_ERRORS_PREFIX . $order_number, \json_encode($errors), $dt);
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
     * @return boolean
     */
    public static function getPaymentMethodsByCountry(string $country)
    {
        $country = strtolower($country);
        $result = [];
        foreach (PaymentProviders::$list as $providerId => $provider)
        {
            $is_pass_method = \App::environment() === 'production' ? $provider['in_prod'] : true;
            if ($provider['is_active'] && $is_pass_method)
            {
                $result[$providerId] = [];

                //check every method of provider
                foreach ($provider['methods'] as $methodId => $method)
                {
                    if (PaymentMethods::$list[$methodId]['is_active'])
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
                        $method             = PaymentMethods::$list[$methodId];
                        $methodData['name'] = $method['name'];
                        $methodData['logo'] = $method['logo'];
                        if (isset($provider['extra_fields']) && isset($provider['extra_fields'][$country])) {
                            $methodData['extra_fields'] = $provider['extra_fields'][$country];
                        }
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
     * @param   string $pref default=checkoutcom
     * @param   array  $excl default=[]
     * @return  string|null
     */
    public static function getProviderByCountryAndMethod(string $country, string $method, string $pref = PaymentProviders::CHECKOUTCOM, array $excl = []): ?string
    {
        $providers = self::getPaymentMethodsByCountry($country);

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
     * @param  string $card_type
     * @param  string $country
     * @param  array|null $ipqs
     * @return object
     */
    private static function checkIs3dsNeeded(string $card_type, string $country, ?array $ipqs): bool
    {
        $result = true;
        $setting = PaymentProviders::$list[PaymentProviders::CHECKOUTCOM]['methods'][$card_type] ?? [];
        $fraud_chance = !empty($ipqs) ? (int)$ipqs['fraud_chance'] : PaymentService::FRAUD_CHANCE_MAX;

        if ($fraud_chance < PaymentService::FRAUD_CHANCE_LIMIT) {
            if (in_array($country, $setting['+3ds'] ?? []) ) {
                $result = true;
            } else if (in_array('*', $setting['-3ds'] ?? []) || in_array($country, $setting['-3ds'] ?? [])) {
                $result = false;
            }
        }

        return $result;
    }
}
