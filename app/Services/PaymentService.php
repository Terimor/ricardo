<?php
namespace App\Services;

use App\Models\CustomerBlacklist;
use App\Models\Domain;
use App\Models\PaymentApi;
use App\Models\Txn;
use App\Models\Currency;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Models\OdinCustomer;
use App\Models\AffiliateSetting;
use App\Constants\PaymentProviders;
use App\Constants\PaymentMethods;
use App\Exceptions\AuthException;
use App\Exceptions\CustomerUpdateException;
use App\Exceptions\InvalidParamsException;
use App\Exceptions\PaymentException;
use App\Exceptions\OrderUpdateException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Payment Service class
 */
class PaymentService
{
    const FRAUD_CHANCE_MAX  = 100;

    const THROW_IS_IP_ABUSED    = true;

    const SUCCESS_PATH  =   '/checkout';
    const FAILURE_PATH  =   '/checkout';

    const STATUS_OK   = 'ok';
    const STATUS_FAIL = 'fail';

    const CACHE_ERRORS_PREFIX   = 'PayErrors';
    const CACHE_ERRORS_TTL_MIN  = 1;

    const BILLING_DESCRIPTOR_MAX_LENGTH = 20;
    const BILLING_DESCRIPTOR_COUNTRIES = ['us', 'ca'];
    const BILLING_DESCRIPTOR_COUNTRIES_CODE = '888-7438103';
    const BILLING_DESCRIPTOR_OTHERS_CODE = '+44-1782454716';

    /**
     * Adds a new OdinOrder
     * @param array $data
     * @return OdinOrder
     * @throws OrderUpdateException
     */
    public static function addOrder(array $data): OdinOrder
    {
        $reply = (new OrderService())->addOdinOrder(
            array_merge(
                ['status' => OdinOrder::STATUS_NEW],
                [
                    'total_chargeback_usd' => 0,
                    'total_refunded_usd' => 0,
                    'total_chargeback' => 0,
                    'total_paid_usd' => 0,
                    'txns_fee_usd' => 0,
                    'total_paid' => 0,
                    'is_invoice_sent' => false,
                    'is_survey_sent' => false,
                    'is_flagged' => false,
                    'is_paused' => false,
                    'txns' => []
                ],
                $data
            ),
            true
        );

        if (isset($reply['errors'])) {
            throw new OrderUpdateException(json_encode($reply['errors']));
        }

        return $reply['order'];
    }

    /**
     * Adds txn data to Order
     * @param  OdinOrder $order
     * @param  array $data
     * @param  array $details
     * @return void
     */
    public static function addTxnToOrder(OdinOrder &$order, array $data, array $details): void
    {
        // log txn
        self::logTxn(array_merge($data, ['payment_method' => $details['payment_method']]));

        $order->addTxn([
            'hash'              => (string)$data['hash'],
            'capture_hash'      => $data['capture_hash'] ?? null,
            'value'             => $data['value'],
            'status'            => $data['status'],
            'fee_usd'           => 0,
            'card_type'         => $details['card_type'] ?? null,
            'card_number'       => $details['card_number'] ?? null,
            'is_fallback'       => $details['is_fallback'] ?? false,
            'payment_method'    => $details['payment_method'],
            'payment_provider'  => $data['payment_provider'],
            'payment_api_id'    => $data['payment_api_id'] ?? null,
            'payer_id'          => $data['payer_id'] ?? null
        ]);
    }

    /**
     * Approves order
     * @param array $data ['hash'=>string,'number'=>?string,'value'=>?float,'status'=>string]
     * @param string|null $provider
     * @return OdinOrder|null
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public static function approveOrder(array $data, ?string $provider = null): ?OdinOrder
    {
        $order = null;
        if (!empty($data['number'])) {
            $order = OdinOrder::getByNumber($data['number']); // throwable
        } elseif (!empty($data['hash']) && $provider) {
            $order = OdinOrder::getByTxnHash($data['hash'], $provider); // throwable
        } else {
            logger()->warning('Order approve failed', $data);
            return null;
        }

        // check webhook reply
        $is_order_need_to_check = false;
        if ($order->status === OdinOrder::STATUS_CANCELLED) {
            logger()->info("Webhook cancelled order [{$order->number}]", ['data' => $data]);
            $is_order_need_to_check = true;
            $order->addNote('Odin: Payment confirmation is received when the order was already canceled');
        } elseif (!in_array($order->status, [OdinOrder::STATUS_NEW, OdinOrder::STATUS_HALFPAID])) {
            logger()->info("Webhook ignored, order [{$order->number}] status [{$order->status}]", ['data' => $data]);
            return $order;
        }

        $txn = $order->getTxnByHash($data['hash']); // throwable
        $txn = array_merge($txn, $data);
        $order->addTxn($txn);

        if ($txn['status'] === Txn::STATUS_APPROVED) {
            $products = $order->getProductsByTxnHash($txn['hash']);
            if (empty($products)) {
                $products = $order->getProductsByTxnValue($txn['value']);
            }

            $is_main = true;
            foreach ($products as $product) {
                $is_main = $product['is_main'];
                if ($product['is_paid']) {
                    $is_order_need_to_check = true;
                }
                $product['txn_hash'] = $txn['hash'];
                $product['is_paid'] = true;
                $order->addProduct($product);
            }

            if ($is_main) {
                $order->is_flagged = false;
                if (!self::isApm($txn['payment_method']) && $txn['card_number']) {
                    CardService::incCachedCardUsageLimit($txn['card_number']);
                }
            }

            $order = OrderService::calcTotalPaid($order);

            $txn_amount_usd = CurrencyService::roundValueByCurrencyRules($txn['value'] / $order->exchange_rate);
            $order->addNote(CustomerBlacklistService::getOrderPauseReason($order, $txn_amount_usd), true);

            $order->status = self::getOrderStatus($order, $is_order_need_to_check);
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
     * Updates or adds customer
     * @param array $contacts
     * @return OdinCustomer
     * @throws CustomerUpdateException
     */
    public static function addCustomer(array $contacts): OdinCustomer
    {
        $reply = (new CustomerService)->addOrUpdate(
            array_merge($contacts,
                [
                    'doc_id' => $contacts['document_number'] ?? null,
                    'phone' => $contacts['phone']['country_code'] . UtilsService::preparePhone($contacts['phone']['number'])
                ]
            ),
            true
        );

        if (isset($reply['errors'])) {
            throw new CustomerUpdateException(json_encode($reply['errors']));
        }

        return $reply['customer'];
    }

    /**
     * Caches webhook errors
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public static function cacheOrderErrors(array $data = []): void
    {
        $order_number = $data['number'] ?? null;
        $errors = $data['errors'] ?? null;
        if ($order_number && !empty($errors)) {
            $dt = (new \DateTime())->add(new \DateInterval("PT" . self::CACHE_ERRORS_TTL_MIN . "M"));
            Cache::put(self::CACHE_ERRORS_PREFIX . $order_number, json_encode($errors), $dt);
        }
    }

    /**
     * Checks if method exists for specified country
     * @param string $country
     * @param array $methodCountries
     * @return bool
     */
    public static function checkIfMethodInCountries(string $country, array $methodCountries): bool
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
     * @param  string $prv Payment provider
     * @param  string|null $affid
     * @param  array  $ipqs
     * @return bool
     */
    public static function checkIs3dsNeeded(string $method, string $country, string $prv, ?string $affid = null, array $ipqs = []): bool
    {
        $result = true;
        $setting = PaymentProviders::$list[$prv]['methods']['main'][$method] ?? [];
        $fraud_chance = $ipqs['fraud_chance'] ?? PaymentService::FRAUD_CHANCE_MAX;

        $fraud_setting = PaymentProviders::$list[$prv]['fraud_setting']['common'];
        if ($affid) {
            $affiliate = AffiliateSetting::getByHasOfferId($affid);
            $fraud_setting = PaymentProviders::$list[$prv]['fraud_setting'][optional($affiliate)->is_3ds_off ? 'affiliate' : 'common'];
        }

        if ($fraud_chance < $fraud_setting['3ds_limit']) {
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
     * @param  string   $prv
     * @param  string|null $affid
     * @param  array|null  $ipqs    default=[]
     * @param  array|null  $details default=[]
     * @return bool
     */
    public static function checkIsFallbackAvailable(string $prv, ?string $affid = null, ?array $ipqs = [], ?array $details = []): bool
    {
        $fraud_chance = $ipqs['fraud_chance'] ?? PaymentService::FRAUD_CHANCE_MAX;

        $fraud_setting = PaymentProviders::$list[$prv]['fraud_setting']['common'];
        if ($affid) {
            $affiliate = AffiliateSetting::getByHasOfferId($affid);
            $fraud_setting = PaymentProviders::$list[$prv]['fraud_setting'][optional($affiliate)->is_3ds_off ? 'affiliate' : 'common'];
        }

        $result = $fraud_chance < $fraud_setting['fallback_limit'];

        if ($prv === PaymentProviders::EBANX) {
            $result = !empty($details['installments']) && $details['installments'] <= EbanxService::INSTALLMENTS_MIN;
        }

        return $result;
    }

    /**
     * Checks(Changes) order currency
     * @param OdinOrder $order
     * @param string $provider
     * @return OdinOrder
     * @throws InvalidParamsException
     * @throws \App\Exceptions\ProductNotFoundException
     */
    public static function checkOrderCurrency(OdinOrder $order, string $provider = PaymentProviders::MINTE): OdinOrder
    {
        $order_product = $order->getMainProduct(); // throwable

        $product = null;
        if ($order_product['price_set']) {
            $product = OdinProduct::getByCopId($order_product['price_set']);
        }
        if (!$product) {
            $product = OdinProduct::getBySku($order_product['sku_code']); // throwable
        }

        // NOTE: prevent implicit currency definition
        $product->currency = $order->currency;

        $price = self::getLocalizedPrice($product, $order_product['quantity'], $order->shipping_country, $provider); // throwable

        if ($order->currency === $price['currency']) {
            return $order;
        }

        logger()->info("Fallback [{$order->number}] change currency {$order->currency} -> {$price['currency']}");

        $order_product = self::createOrderProduct($order_product['sku_code'], $price, ['is_warranty' => !!$order_product['warranty_price']]);

        $order->currency        = $price['currency'];
        $order->exchange_rate   = $price['usd_rate'];
        $order->total_price     = $order_product['total_price'];
        $order->total_price_usd = $order_product['total_price_usd'];
        $order->addProduct($order_product, true);

        return $order;
    }

    /**
     * Creates OdinOrder['products'] item
     * @param  string      $sku
     * @param  array       $price
     * @param  array       $details [is_main => bool, is_plus_one => bool, is_warranty => bool]
     * @return array
     */
    public static function createOrderProduct(string $sku, array $price, array $details = []): array
    {
        $is_main = $details['is_main'] ?? true;
        $order_product = [
            'sku_code'              => $sku,
            'quantity'              => $price['quantity'],
            'price'                 => CurrencyService::roundValueByCurrencyRules($price['value'], $price['currency']),
            'price_usd'             => CurrencyService::roundValueByCurrencyRules($price['value_usd']),
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
            'total_price_usd'       => CurrencyService::roundValueByCurrencyRules($price['value_usd'])
        ];

        $is_warranty = $details['is_warranty'] ?? false;
        if ($is_warranty) {
            $order_product['warranty_price']        = CurrencyService::roundValueByCurrencyRules($price['warranty_value'], $price['currency']);
            $order_product['warranty_price_usd']    = CurrencyService::roundValueByCurrencyRules($price['warranty_value_usd']);
            $order_product['total_price']           = CurrencyService::roundValueByCurrencyRules($price['value'] + $price['warranty_value'], $price['currency']);
            $order_product['total_price_usd']       = CurrencyService::roundValueByCurrencyRules($order_product['total_price'] / $price['usd_rate']);
        }
        return $order_product;
    }

    /**
     * Invokes fallback for failed 3ds transaction
     * @param OdinOrder $order
     * @param array $order_txn
     * @param array $details
     * @return array
     * @throws InvalidParamsException
     * @throws OrderUpdateException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws PaymentException
     */
    public static function fallback3ds(OdinOrder $order, array $order_txn, array $details = []): array
    {
        $card = CardService::getCachedCardAndCheckUsageLimit($order_txn['card_number'], false);
        $order_product = $order->getMainProduct(false);

        $result = ['status' => Txn::STATUS_FAILED, 'errors' => [], 'value' => $order_txn['value']];
        if (!$card || !$order_product) {
            logger()->info("Pre-Fallback [{$order->number}]", ['card' => (bool)$card, 'order_product' => (bool)$order_product]);
            return $result;
        }

        $reply = self::fallbackOrder($order, $card, array_merge([
            'provider' => $order_txn['payment_provider'],
            'method' => $order_txn['payment_method']
        ], $details));

        if ($reply['status']) {
            $result = $reply['payment'];

            $order_product['txn_hash'] = $reply['payment']['hash'];
            $order->addProduct($order_product, true);
            PaymentService::addTxnToOrder($order, $reply['payment'], array_merge($order_txn, ['is_fallback' => true]));
            $order->is_flagged = $reply['payment']['is_flagged'];

            if (!$order->save()) {
                $validator = $order->validate();
                if ($validator->fails()) {
                    throw new OrderUpdateException(json_encode($validator->errors()->all()));
                }
            }
        }

        return $result;
    }

    /**
     * Invokes fallback
     * @param OdinOrder $order
     * @param array $card
     * @param array $details ['provider' => string, 'method' => string, 'useragent' => ?string]
     * @return array
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws InvalidParamsException
     */
    public static function fallbackOrder(OdinOrder &$order, array $card, array $details): array
    {
        $is_fallback_available = self::checkIsFallbackAvailable(
            $details['provider'], $order->affiliate, $order->ipqualityscore, ['installments' => $order->installments]
        );

        $result = ['status' => false, 'payment' => []];

        $order_product = $order->getMainProduct(false) ?? ['sku_code' => ''];
        $product = OdinProduct::getBySku($order_product['sku_code'], false);

        if (!$is_fallback_available || !$product) {
            logger()->info("Fallback is not available [{$order->number}] for provider {$details['provider']}");
            return $result;
        }

        $domain = Domain::getByName();
        $api = PaymentApiService::getAvailableOne(
            $product->getIdAttribute(),
            $details['method'],
            optional($domain)->getIdAttribute(),
            self::getProvidersForPay($order->shipping_country, $details['method'], false, [$details['provider']]),
            $order->currency
        );

        if (!$api) {
            logger()->info("Fallback api not found [{$order->number}] for provider [{$details['provider']}]");
            return $result;
        } elseif ($api->payment_provider === $details['provider']) {
            logger()->info("Fallback api cannot be the same [{$details['provider']}]");
            return $result;
        }

        logger()->info("Fallback [{$order->number}], sequence [{$details['provider']}->{$api->payment_provider}]");

        switch ($api->payment_provider):
            case PaymentProviders::BLUESNAP:
                $result['status']  = true;
                $order = self::checkOrderCurrency($order, PaymentProviders::BLUESNAP);
                $handler = new BluesnapService($api);
                $result['payment'] = $handler->payByCard(
                    $card,
                    [
                        'street'     => $order->shipping_street,
                        'city'       => $order->shipping_city,
                        'country'    => $order->shipping_country,
                        'state'      => $order->shipping_state,
                        'building'   => $order->shipping_building,
                        'complement' => $order->shipping_apt,
                        'zip'        => $order->shipping_zip,
                        'email'      => $order->customer_email,
                        'first_name' => $order->customer_first_name,
                        'last_name'  => $order->customer_last_name,
                        'phone'      => $order->customer_phone,
                        'ip'         => $order->ip,
                        'document_number' => $order->customer_doc_id
                    ],
                    [
                        '3ds' => self::checkIs3dsNeeded(
                            $details['method'],
                            $order->shipping_country,
                            PaymentProviders::BLUESNAP,
                            $order->affiliate,
                            (array)$order->ipqualityscore
                        ),
                        'amount' => $order->total_price,
                        'currency' => $order->currency,
                        'order_id' => $order->getIdAttribute(),
                        'descriptor' => $product->billing_descriptor,
                        'descriptor_phone' => self::getBillingDescriptorCodeByCountry($order->shipping_country)
                    ]
                );
                break;
            case PaymentProviders::MINTE:
                $order = self::checkOrderCurrency($order, PaymentProviders::MINTE);
                $handler = new MinteService($api);
                $result['status']  = true;
                $payment = $handler->payByCard(
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
                        '3ds' => self::checkIs3dsNeeded(
                            $details['method'],
                            $order->shipping_country,
                            PaymentProviders::MINTE,
                            $order->affiliate,
                            (array)$order->ipqualityscore
                        ),
                        'amount'    => $order->total_price,
                        'currency'  => $order->currency,
                        'domain'    => optional($domain)->name,
                        'order_id'  => $order->getIdAttribute(),
                        'order_number'  => $order->number,
                        'descriptor'    => $order->billing_descriptor,
                        'user_agent'    => $details['useragent'] ?? null
                    ]
                );
                if ($payment['status'] === Txn::STATUS_CAPTURED) {
                    $capture = $handler->capture($payment['hash'], ['amount' => $order->total_price, 'currency' => $order->currency]);
                    self::logTxn(array_merge($capture, ['payment_method' => $details['method']]));
                    $payment['status'] = $capture['status'];
                    $payment['capture_hash'] = $capture['hash'];
                }
                $result['payment'] = $payment;
                break;
            case PaymentProviders::EBANX:
                $handler = new EbanxService($api);
                $result['status'] = true;
                $result['payment'] = $handler->payByCard(
                    $card,
                    [
                        'street'            => $order->shipping_street,
                        'city'              => $order->shipping_city,
                        'country'           => $order->shipping_country,
                        'state'             => $order->shipping_state,
                        'building'          => $order->shipping_building,
                        'complement'        => $order->shipping_apt,
                        'zip'               => $order->shipping_zip,
                        'document_number'   => $order->customer_doc_id,
                        'email'             => $order->customer_email,
                        'first_name'        => $order->customer_first_name,
                        'last_name'         => $order->customer_last_name,
                        'phone'             => $order->customer_phone,
                        'ip'                => $order->ip
                    ],
                    [
                        [
                            'sku'   => $order_product['sku_code'],
                            'qty'   => $order_product['quantity'],
                            'name'  => $product->product_name,
                            'desc'  => $product->description,
                            'amount'    => $order_product['price'],
                            'is_main'   => true
                        ]
                    ],
                    [
                        'amount'        => $order->total_price,
                        'currency'      => $order->currency,
                        'number'        => $order->number,
                        'installments'  => $order->installments
                    ]
                );
                break;
            case PaymentProviders::STRIPE:
                $result['status'] = true;
                $order = self::checkOrderCurrency($order, PaymentProviders::STRIPE);
                $handle = new StripeService($api);
                $result['payment'] = $handle->payByCard(
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
                    ],
                    [
                        '3ds' => PaymentService::checkIs3dsNeeded(
                            $details['method'],
                            $order->shipping_country,
                            PaymentProviders::STRIPE,
                            $order->affiliate,
                            (array)$order->ipqualityscore
                        ),
                        'amount'    => $order->total_price,
                        'currency'  => $order->currency,
                        'order_id'  => $order->getIdAttribute(),
                        'order_number'  => $order->number,
                        'descriptor'    => $order->billing_descriptor,
                        'installments' => $order->installments,
                        'billing_descriptor' => $product->getOriginal('billing_descriptor')
                    ]
                );
                break;
            default:
                logger()->info("Fallback [{$order->number}] provider not found");
        endswitch;

        return $result;
    }

    /**
     * Checks payment to fraud
     * @param array $ipqs
     * @param string $prv Payment provider
     * @param string|null $affid
     * @param string|null $email
     * @return void
     * @throws PaymentException
     */
    public static function fraudCheck(?array $ipqs, string $prv, ?string $affid = null, ?string $email = null): void
    {
        if (!empty($ipqs) && \App::environment() === 'production') {
            $isTrusted = null;
            if ($email) {
                $isTrusted = OdinCustomer::isTrustedByEmail($email);
            }
            if ($isTrusted === null) {
                $fraud_chance = $ipqs['fraud_chance'] ?? PaymentService::FRAUD_CHANCE_MAX;
                $is_bot = $ipqs['bot_status'] ?? false;
                $is_valid_email = !empty($ipqs['transaction_details']) ? $ipqs['transaction_details']['valid_billing_email'] ?? null : null;

                $fraud_setting = PaymentProviders::$list[$prv]['fraud_setting']['common'];
                if ($affid) {
                    $affiliate = AffiliateSetting::getByHasOfferId($affid);
                    $fraud_setting = PaymentProviders::$list[$prv]['fraud_setting'][optional($affiliate)->is_3ds_off ? 'affiliate' : 'common'];
                }

                if ($fraud_chance > $fraud_setting['refuse_limit'] || $is_bot || $is_valid_email === false) {
                    throw new PaymentException('Payment is refused', 'card.error.refused');
                }
            } elseif ($isTrusted === false) {
                logger()->info("Payment is refused for blocked customer [{$email}]");
                throw new PaymentException('Payment is refused', 'card.error.refused');
            }
        }
    }

    /**
     * Generates result for createOrder method
     * @param OdinOrder $order
     * @param array $payment
     * @return array
     * @throws \Exception
     */
    public static function generateCreateOrderResult(OdinOrder $order, array $payment): array
    {
        $result = [
            'order_currency' => $order->currency,
            'order_number'   => $order->number,
            'order_amount'   => $payment['value'],
            'order_id'       => $order->getIdAttribute(),
            'status'         => self::STATUS_FAIL,
            'id'             => $payment['hash'] ?? null
        ];

        if ($payment['status'] !== Txn::STATUS_FAILED) {
            $result['status'] = self::STATUS_OK;
            $result['redirect_url'] = !empty($payment['redirect_url']) ? stripslashes($payment['redirect_url']) : null;
            $result['bs_pf_token'] = $payment['bs_pf_token'] ?? null;
        } else {
            $result['errors'] = $payment['errors'];
            self::cacheOrderErrors(['number' => $order->number, 'errors' => $payment['errors']]);
        }

        return array_filter($result);
    }

    /**
     * Generates result of upsells order
     * @param OdinOrder $order
     * @param array $upsells
     * @param array $payment
     * @return array
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public static function generateCreateUpsellsOrderResult(OdinOrder $order, array $upsells, array $payment): array
    {
        if (in_array($payment['status'], [Txn::STATUS_AUTHORIZED, Txn::STATUS_CAPTURED, Txn::STATUS_APPROVED])) {
            foreach ($upsells as $key => $item) {
                if (!isset($item['status'])) {
                    $upsells[$key]['status'] = self::STATUS_OK;
                }
            }
        } else {
            foreach ($upsells as $key => $item) {
                $upsells[$key]['status'] = self::STATUS_FAIL;
            }
        }

        // approve order if txn is approved
        if ($payment['status'] === Txn::STATUS_APPROVED) {
            $order = PaymentService::approveOrder($payment, $payment['payment_provider']);
        }

        return array_filter([
            'errors'         => $payment['errors'] ?? null,
            'id'             => $payment['hash'] ?? null,
            'order_id'       => $order->getIdAttribute(),
            'order_number'   => $order->number,
            'order_currency' => $order->currency,
            'redirect_url'   => $payment['redirect_url'] ?? null,
            'status'         => $payment['status'] !== Txn::STATUS_FAILED ? self::STATUS_OK : self::STATUS_FAIL,
            'upsells'        => $upsells,
        ]);
    }

    /**
     * Returns billing descriptor code by country
     * @param string $country
     * @return string
     */
    public static function getBillingDescriptorCodeByCountry(string $country): string
    {
        if (in_array($country, self::BILLING_DESCRIPTOR_COUNTRIES)) {
            return self::BILLING_DESCRIPTOR_COUNTRIES_CODE;
        }
        return self::BILLING_DESCRIPTOR_OTHERS_CODE;
    }

    /**
     * Get Order errors
     * @param string $order_id
     * @return array
     * @throws \App\Exceptions\OrderNotFoundException
     */
    public static function getCachedOrderErrors(string $order_id): array
    {
        $order = OdinOrder::getById($order_id);
        $cache_reply = Cache::get(self::CACHE_ERRORS_PREFIX . $order->number);
        return $cache_reply ? (json_decode($cache_reply, true) ?? []) : [];
    }

    /**
     * Returns localized price
     * @param  OdinProduct $product
     * @param  int         $qty
     * @param  string      $country
     * @param  string      $provider
     * @return array
     * @throws InvalidParamsException
     */
    public static function getLocalizedPrice(OdinProduct $product, int $qty, string $country, string $provider): array
    {
        // NOTE: implicit definition currency
        $localized_product = (new ProductService())->localizeProduct($product);
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
        $currency_code = self::getSupportedCurrency($country, $currency->code, $provider);

        if ($currency_code === Currency::DEF_CUR) {
            $price = $price_usd;
            $price_warranty = $price_warranty_usd;
            $usd_rate = 1;
        }

        return [
            'currency'  => $currency_code,
            'price_set' => $product->prices['price_set'] ?? '',
            'quantity'  => $qty,
            'usd_rate'  => $usd_rate,
            'value'     => $price,
            'value_usd' => $price_usd,
            'warranty_value' => $price_warranty,
            'warranty_value_usd' => $price_warranty_usd
        ];
    }

    /**
     * Returns supported currency or default
     * @param string $country
     * @param string $currency
     * @param string $provider
     * @return string
     */
    public static function getSupportedCurrency(string $country, string $currency, string $provider): string
    {
        return self::isCurrencySupported($country, $currency, $provider) ? $currency : Currency::DEF_CUR;
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
     * @param string|null $currency
     * @param bool $is_main default=true
     * @return array
     */
    public static function getPaymentMethodsByCountry(string $country, ?string $currency = null, bool $is_main = true): array
    {
        $country = strtolower($country);
        $result = [];
        foreach (PaymentProviders::$list as $providerId => $provider) {
            $is_currency_supported = $currency ? self::isCurrencySupported($country, $currency, $providerId) : true;
            if (PaymentProviders::isActive($providerId, $is_main) && $is_currency_supported) {
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
                        $method = PaymentMethods::$list[$methodId];
                        $methodData['name'] = $method['name'];
                        $methodData['is_apm'] = $method['is_apm'];
                        $methodData['logo'] = UtilsService::getCdnUrl(true).$method['logo'];
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
     * @param string $country
     * @param string $method
     * @param bool $is_main
     * @param array $excl default=[]
     * @return  array
     */
    public static function getProvidersForPay(string $country, string $method, bool $is_main = true, array $excl = []): array
    {
        $providers = self::getPaymentMethodsByCountry($country, null, $is_main);

        if (!EbanxService::isCountrySupported($country)) {
            $excl[] = PaymentProviders::EBANX;
        }

        if (!AppmaxService::isCountrySupported($country)) {
            $excl[] = PaymentProviders::APPMAX;
        }

        $available_providers = [];
        foreach ($providers as $prv => $methods) {
            if (isset($methods[$method]) && !in_array($prv, $excl)) {
                $available_providers[] = $prv;
            }
        }
        return $available_providers;
    }

    /**
     * Returns OdinProduct by cop_id or sku
     * @param string|null $cop_id
     * @param string|null $sku
     * @return OdinProduct|null
     * @throws \App\Exceptions\ProductNotFoundException
     */
    public static function getProductByCopIdOrSku(?string $cop_id, ?string $sku): ?OdinProduct
    {
        $product = OdinProduct::getByCopId($cop_id);

        if (!$product && $sku) {
            $product = OdinProduct::getBySku($sku); // throwable
        }
        return $product;
    }

    /**
     * Determines the order status by the total amount
     * @param OdinOrder $order
     * @param bool $need_to_check
     * @return string
     */
    public static function getOrderStatus(OdinOrder $order, bool $need_to_check = false): string
    {
        $status = OdinOrder::STATUS_NEW;
        if ($need_to_check) {
            $status = OdinOrder::STATUS_ERROR;
        } elseif ($order->total_paid) {
            $price_paid_diff = (floor($order->total_paid * 100) - floor($order->total_price * 100)) / 100;
            $status = $price_paid_diff >= 0 ? OdinOrder::STATUS_PAID : OdinOrder::STATUS_HALFPAID;
        }
        return $status;
    }

    /**
     * Checks if it's APM
     * @param string $method
     * @return bool [description]
     */
    public static function isApm(string $method): bool
    {
        return PaymentMethods::$list[$method]['is_apm'];
    }

    /**
     * Checks is the currency supported
     * @param  string $country
     * @param  string $currency
     * @param  string $provider
     * @return bool
     */
    public static function isCurrencySupported(string $country, string $currency, string $provider): bool
    {
        $result = true;
        switch ($provider):
            case PaymentProviders::EBANX:
                $result = EbanxService::isCurrencySupported($country, $currency);
                break;
            case PaymentProviders::MINTE:
                $result = MinteService::isCurrencySupported($country, $currency);
                break;
            case PaymentProviders::APPMAX:
                $result = AppmaxService::isCurrencySupported($country, $currency);
                break;
            case PaymentProviders::STRIPE:
                $result = StripeService::isCurrencySupported($currency);
                break;
            case PaymentProviders::NOVALNET:
                $result = NovalnetService::isCurrencySupported($country, $currency);
                break;
        endswitch;

        return $result;
    }

    /**
     * Stores transaction info in txn collection
     * @param array $data
     * @return void
     */
    public static function logTxn(array $data): void
    {
        (new OrderService())->addTxn([
            'hash'              => $data['hash'],
            'value'             => $data['value'],
            'currency'          => $data['currency'],
            'provider_data'     => $data['provider_data'],
            'payment_method'    => $data['payment_method'],
            'payment_provider'  => $data['payment_provider'],
            'payer_id'          => $data['payer_id'] ?? null
        ]);
    }

    /**
     * Processes novalnet webhook
     * @param array $notification
     * @param string $order_id
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     * @throws AuthException
     * @throws OrderUpdateException
     */
    public static function processNovalnetWebhook(array $notification, string $order_id): void
    {
        $order = OdinOrder::getById($order_id); // throwable
        $order_txn = $order->getTxnByHash(Arr::get($notification, 'transaction.tid')); // throwable

        $handler = new NovalnetService(PaymentApi::getById($order_txn['payment_api_id']));

        if (!$handler->verifyWebhook($notification)) {
            throw new AuthException('Unauthorized');
        } elseif (!$handler->verifyWebhookEvent($notification)) {
            logger()->info('Novalnet wh unprocessed event', $notification);
        } else {
            self::approveOrder([
                'number' => $order->number,
                'hash' => $order_txn['hash'],
                'status' => NovalnetService::mapResponseStatus($notification)
            ]);
        }
    }

    /**
     * Processes client return after redirect
     * @param array $params
     * @param string $order_id
     * @return array
     * @throws AuthException
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public static function processNovalnetReturnClient(array $params, string $order_id): array
    {
        $order = OdinOrder::getById($order_id); // throwable
        $order_txn = $order->getTxnByHash("tsec_{$params['tsec']}"); // throwable
        $order_product = $order->getProductByTxnHash($order_txn['hash']); // throwable

        $handler = new NovalnetService(PaymentApi::getById($order_txn['payment_api_id']));

        if (!$handler->verifyRetCli($params)) {
            throw new AuthException('Unauthorized');
        }

        $payment = $handler->getPayment($params['tid'], $order->currency, $order_txn['value']);

        // rewrite order->txns
        $order->dropTxn($order_txn['hash']);
        PaymentService::addTxnToOrder($order, $payment, $order_txn);

        // bind new txn to product
        $order->dropProduct($order_product['sku_code'], $order_txn['hash']);
        $order_product['txn_hash'] = $payment['hash'];
        $order->addProduct($order_product);

        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }

        if ($payment['status'] == Txn::STATUS_APPROVED) {
            self::approveOrder(['number' => $order->number, 'hash' => $payment['hash'], 'status' => $payment['status']]);
        }

        return [
            'is_apm' => PaymentService::isApm($order_txn['payment_method']),
            'is_main' => $order_product['is_main'],
            'status' => $payment['status'] !== Txn::STATUS_FAILED ? PaymentService::STATUS_OK : PaymentService::STATUS_FAIL
        ];
    }

    /**
     * Tries to refund payment
     * @param string $order_id
     * @param string $txn_hash
     * @param string $reason
     * @param float|null $amount
     * @return array
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public static function refund(string $order_id, string $txn_hash, string $reason, ?float $amount): array
    {
        $order = OdinOrder::getById($order_id); // throwable
        $txn = $order->getTxnByHash($txn_hash); // throwable

        $result = ['status' => false];
        if ($txn['status'] === Txn::STATUS_APPROVED) {
            $api = PaymentApiService::getById($txn['payment_api_id']);
            switch (optional($api)->payment_provider):
                case PaymentProviders::APPMAX:
                    $type = AppmaxService::TYPE_REFUND_FULL;
                    if ($amount && $amount < $txn['value']) {
                        $type = AppmaxService::TYPE_REFUND_PART;
                    }
                    $result = (new AppmaxService($api))->refund($txn_hash, $type, $amount ?? $txn['value']);
                    break;
                case PaymentProviders::CHECKOUTCOM:
                    $result = (new CheckoutDotComService($api))->refund($txn_hash, $order->number, $order->currency, $amount);
                    break;
                case PaymentProviders::BLUESNAP:
                    $result = (new BluesnapService($api))->refund($txn_hash, $amount);
                    break;
                case PaymentProviders::EBANX:
                    $result = (new EbanxService($api))->refund($txn_hash, $order->currency, $amount ?? $txn['value'], $reason);
                    break;
                case PaymentProviders::MINTE:
                    if (self::isApm($txn['payment_method'])) {
                        $result = (new MinteService($api))->refund($txn_hash, $amount ?? $txn['value']);
                    } elseif (!empty($txn['capture_hash'])) {
                        $result = (new MinteService($api))->refund($txn['capture_hash'], $amount ?? $txn['value']);
                    } else {
                        $result['errors'] = ["Transaction [$txn_hash] cannot be refunded"];
                    }
                    break;
                case PaymentProviders::STRIPE:
                    $result = (new StripeService($api))->refund($txn_hash, $order->currency, $amount);
                    break;
                default:
                    $result['errors'] = ["Refund for {$txn['payment_provider']} not implemented yet. [$txn_hash]"];
                    logger()->info("PaymentService: refund for {$txn['payment_provider']} not implemented yet");
            endswitch;
        } else {
            $result['errors'] = ["Transaction [$txn_hash] is not approved"];
        }
        return $result;
    }

    /**
     * Reject txn
     * @param array $data
     * @param string|null $provider
     * @return OdinOrder|null
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public static function rejectTxn(array $data, ?string $provider = null): ?OdinOrder
    {
        $order = null;
        if (!empty($data['number'])) {
            $order = OdinOrder::getByNumber($data['number']); // throwable
        } elseif (!empty($data['hash']) && $provider) {
            $order = OdinOrder::getByTxnHash($data['hash'], $provider); // throwable
        } else {
            logger()->warning('Order txn reject failed', $data);
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

    public static function test(array $params)
    {
        $model = CustomerBlacklist::searchOne($params);
        return optional($model)->toArray();
//        return (string) CustomerBlacklist::addOrUpdateOne($params);
//        return self::getProvidersForPay('at', PaymentMethods::VISA, false);
    }

}
