<?php

namespace App\Services;

use App\Constants\PaymentProviders;
use App\Exceptions\PaymentException;
use App\Exceptions\PPCurrencyNotSupportedException;
use App\Http\Requests\PayPalCrateOrderRequest;
use App\Http\Requests\PayPalVerfifyOrderRequest;
use App\Models\Currency;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Models\Txn;
use App\Models\PaymentApi;
use App\Constants\PaymentMethods;
use BraintreeHttp\HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use stdClass;

/**
 * Class PayPalService
 * @package App\Services
 */
class PayPalService
{
    const DEFAULT_CURRENCY = 'USD';

    const STATUS_COMPLETED  = 'COMPLETED';
    const STATUS_PENDING    = 'PENDING';
    const STATUS_DECLINED   = 'DECLINED';

    /**
     * @var PayPalHttpClient
     */
    protected $payPalHttpClient;

    /**
     * @var OrderService
     */
    protected $orderService;

    public static $supported_currencies = [
            'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR', 'ILS', 'JPY', 'MXN', 'TWD', 'NZD',
            'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'
        ];

    /**
     * PayPalService constructor.
     * @param PayPalHttpClient $payPalHttpClient
     * @param OrderService $orderService
     */
    public function __construct(PayPalHttpClient $payPalHttpClient, OrderService $orderService)
    {
        $this->payPalHttpClient = $payPalHttpClient;
        $this->orderService = $orderService;
    }

    /**
     * Processes upsells payment
     * @param PayPalCrateOrderRequest $request
     * @return array
     * @throws PPCurrencyNotSupportedException
     */
    public function createUpsellOrder(PayPalCrateOrderRequest $request): array
    {
        $upsell_order = OdinOrder::find($request->get('order'));
        if (!$upsell_order) {
            abort(404);
        }

        $payment_api = PaymentApi::getActivePaypal();

        // If no upsells selected
        if(!$request->get('upsells')) {
            return [
                'braintree_response' => null,
                'odin_order_id' => optional($upsell_order)->getIdAttribute(),
                'order_currency' => $upsell_order->currency
            ];
        }

        $this->checkIforderAllowedForAddingProducts($upsell_order);
        $product = null;
        if ($request->get('cop_id')) {
            $product = $this->findProductByCopId($request->get('cop_id'));
        }
        if (!$product) {
            $product = $this->findProductBySku($request->get('sku_code'));
        }

        $total_upsell_price = 0;
        $upsell_order_exchange_rate = null;
        $pp_items = $upsell_order_products = [];
        $order_main_product = $upsell_order->getMainProduct(); // throwable

        foreach ($request->get('upsells') as $upsell_id => $upsell_quantity) {
            $product_srv = new ProductService();
            $temp_upsell_product = $product_srv->getUpsellProductById($product, $upsell_id, $upsell_quantity, $upsell_order->currency);
            $temp_upsell_product = $product_srv->localizeUpsell($temp_upsell_product, $order_main_product['sku_code']);
            $temp_upsell_item_price = $temp_upsell_product['upsellPrices'][$upsell_quantity]['price'];
            $upsell_order_exchange_rate = $temp_upsell_product['upsellPrices'][$upsell_quantity]['exchange_rate'];

            // Converting to USD.
            $temp_upsell_item_usd_price = CurrencyService::roundValueByCurrencyRules(
                $temp_upsell_item_price / $upsell_order_exchange_rate,
                self::DEFAULT_CURRENCY);

            $total_upsell_price += $temp_upsell_item_price;

            // Setting PayPal order items
            $pp_items[] = [
                'name' => $temp_upsell_product->product_name,
                'description' => $temp_upsell_product->long_name,
                'unit_amount' => [
                    'currency_code' => $upsell_order->currency,
                    'value' => $temp_upsell_item_price / $upsell_quantity,
                ],
                'quantity' => $upsell_quantity
            ];

            // Creating array of an order upsell products.
            $upsell_order_products[] = [
                'sku_code' => $temp_upsell_product['upsell_sku'],
                'quantity' => (int)$upsell_quantity,
                'price' => $temp_upsell_item_price,
                'price_usd' => $temp_upsell_item_usd_price,
                'warranty_price' => null,
                'warranty_price_usd' => null,
                'is_main' => false,
                'is_paid' => false,
                'is_exported' => false,
                'is_plus_one' => false,
                'price_set' => null,
                'txn_hash' => null,
                'is_upsells' => true,
            ];

        }

        $pp_purchase_unit = [
            'description' => $product->long_name,
            'amount' => [
                'currency_code' => $upsell_order->currency,
                'value' => $total_upsell_price,
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => $upsell_order->currency,
                        'value' => $total_upsell_price,
                    ],
                ]
            ],
            'items' => $pp_items,
        ];

        $pp_request = new OrdersCreateRequest();
        $pp_request->prefer('return=representation');
        $pp_request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [$pp_purchase_unit]
        ];
        $response = null;
        try {
            $response = $this->payPalHttpClient->execute($pp_request);
        } catch (HttpException $e) {
            $shop_currency = CurrencyService::getCurrency($upsell_order->currency);
            $this->handlePPBraintreeException($e, $shop_currency);
        }

        // If success create new txn and update order
        if (isset($response->statusCode) && $response->statusCode === 201) {
            $paypal_order = $response->result;

            PaymentService::addTxnToOrder(
                $upsell_order,
                [
                    'hash' => $paypal_order->id,
                    'value' => (float)$paypal_order->purchase_units[0]->amount->value,
                    'currency' => $paypal_order->purchase_units[0]->amount->currency_code,
                    'status' => Txn::STATUS_AUTHORIZED,
                    'provider_data' => $paypal_order,
                    'payment_provider' => $payment_api->payment_provider,
                    'payment_api_id' => (string)$payment_api->_id
                ],
                ['payment_method' => PaymentMethods::INSTANT_TRANSFER]
            );

            // Setting txn_hash for an upsell products
            $upsell_order_products = collect($upsell_order_products)
                ->transform(function($item, $key) use ($paypal_order, $product) {
                    $item['txn_hash'] = $paypal_order->id;
                    $item['is_plus_one'] = optional($this->findProductBySku($item['sku_code']))->_id === $product->_id;

                    return $item;
                })->toArray();

            // Update main order
            $upsell_order->total_price += $total_upsell_price;
            $upsell_order->total_price_usd = CurrencyService::roundValueByCurrencyRules(
                $upsell_order->total_price / $upsell_order_exchange_rate,
                self::DEFAULT_CURRENCY);
            $upsell_order->status = PaymentService::getOrderStatus($upsell_order);
            $upsell_order->products = array_merge($upsell_order->products, $upsell_order_products);
            $upsell_order->save();
        }

        return [
            'braintree_response' => $response,
            'odin_order_id' => optional($upsell_order)->getIdAttribute(),
            'order_currency' => $upsell_order->currency,
            'order_number' => optional($upsell_order)->number,
        ];
    }

    /**
     * Creating order
     *
     * @param PayPalCrateOrderRequest $request
     * @return array
     * @throws PPCurrencyNotSupportedException
     * @throws PaymentException
     */
    public function createOrder(PayPalCrateOrderRequest $request): array
    {
        $ipqs = $request->input('ipqs', null);
        $fingerprint = $request->get('f', null);
        $payment_api = PaymentApi::getActivePaypal();
        $params = !empty($request->page_checkout) ? \Utils::getParamsFromUrl($request->page_checkout) : null;
        $affId = AffiliateService::getAttributeByPriority($params['aff_id'] ?? null, $params['affid'] ?? null);

        // refuse payment if  there is fraud
        PaymentService::fraudCheck($ipqs, $payment_api->payment_provider, $affId);

        $order = $request->get('order') ? OdinOrder::find($request->get('order')) : null;
        $upsellFlag = $request->get('is_upsell');
        if ($order && $upsellFlag) {
            return $this->createUpsellOrder($request);
        }

        $product = null;
        if ($request->get('cop_id')) {
            $product = $this->findProductByCopId($request->get('cop_id'));
        }
        if (!$product) {
            $product = $this->findProductBySku($request->get('sku_code'));
        }
        $priceData = $this->getPrice($request, $product);
        $price_usd = CurrencyService::roundValueByCurrencyRules($priceData['price'] / $priceData['exchange_rate'], self::DEFAULT_CURRENCY);

        // Currency of the prices show on the shop page
        $shop_currency = CurrencyService::getCurrency();
        $shop_currency_code = $shop_currency->code;

        $local_currency = $priceData['code'];
        $local_price = $priceData['price'];
        $total_price_usd = $price_usd;
        $total_local_price = $local_price;
        $is_currency_supported = in_array($priceData['code'], self::$supported_currencies);
        $pp_currency_code = !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency;
        $local_warranty_price = ($request->input('is_warranty_checked') && $product->warranty_percent) ? CurrencyService::roundValueByCurrencyRules($priceData['warranty'], $priceData['code']) : 0;
        $local_warranty_usd = ($request->input('is_warranty_checked') && $product->warranty_percent) ? CurrencyService::roundValueByCurrencyRules(CurrencyService::calculateWarrantyPrice((float)$product->warranty_percent, $price_usd),self::DEFAULT_CURRENCY): 0;

        if ($is_currency_supported) {
            $subTotal = $total_local_price + $local_warranty_price;
        } else {
            $subTotal = $price_usd + $local_warranty_usd;
        }

        // create new order and transaction after 1 hour
        $expireTime = strtotime("-1 hour");
        if ($order && $order->created_at->toDateTime()->getTimestamp() < $expireTime) {
            $order = null;
        }

        // if order and the same values return current order
        if ($order && $order->total_price == $subTotal && !empty($order->txns[0]['hash'])) {
            $response = new stdClass();
            $response->result = new stdClass();
            $response->result->id = $order->txns[0]['hash'];
        } else {
            // If local currency is not supported by PayPal convert to USD. Used for purchase only.
            $name_for_many = I18nService::t('paypal.product.pack', app()->getLocale(), ['count' => (int)$request->sku_quantity, 'productname' => $product->product_name]);
            $desc_for_many = I18nService::t('paypal.product.pack', app()->getLocale(), ['count' => (int)$request->sku_quantity, 'productname' => $product->long_name]);
            if (!empty($product->unit_qty) && $product->unit_qty > 1) {
                $unit_qty_text = I18nService::t('product.unit_qty.total', app()->getLocale(), ['count' => (int)$request->sku_quantity * $product->unit_qty]);
                $name_for_many .= ' '.$unit_qty_text;
                $desc_for_many .= ' '.$unit_qty_text;
            }
            $items = [[
                'name' => (int)$request->sku_quantity > 1 ? $name_for_many : $product->product_name,
                'description' => (int)$request->sku_quantity > 1 ? $desc_for_many : $product->long_name,
                'sku' => $request->sku_code,
                'unit_amount' => [
                    'currency_code' => $pp_currency_code,
                    'value' => !$is_currency_supported ? $price_usd : $local_price,
                ],
                'quantity' => 1
            ]];

            if ($request->input('is_warranty_checked') && $product->warranty_percent) {
                $local_warranty_price = CurrencyService::roundValueByCurrencyRules($priceData['warranty'], $priceData['code']);
                $total_price_usd = CurrencyService::roundValueByCurrencyRules(
                    ($priceData['price'] + $priceData['warranty']) / $priceData['exchange_rate'],
                    self::DEFAULT_CURRENCY
                );
                $local_warranty_usd = CurrencyService::roundValueByCurrencyRules(
                    CurrencyService::calculateWarrantyPrice((float)$product->warranty_percent, $price_usd),
                    self::DEFAULT_CURRENCY
                );
                $total_local_price += $local_warranty_price;
                $items[] = [
                    'name' => 'Warranty',
                    'description' => 'Warranty',
                    'unit_amount' => [
                        'currency_code' => $pp_currency_code,
                        'value' => !$is_currency_supported ? $local_warranty_usd : $local_warranty_price,
                    ],
                    'quantity' => 1
                ];
            }
            $unit = [
                'description' => (int)$request->sku_quantity > 1 ? $desc_for_many : $product->long_name,
                'amount' => [
                    'currency_code' => $pp_currency_code,
                    'value' => !$is_currency_supported ? $total_price_usd : $total_local_price,
                    'items' => $items,
                ]
            ];

            $pp_request = new OrdersCreateRequest();
            $pp_request->prefer('return=representation');
            $pp_request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [$unit]
            ];
            $response = null;
            try {
                $response = $this->payPalHttpClient->execute($pp_request);
            } catch (HttpException $e) {
                $this->handlePPBraintreeException($e, $shop_currency);
            }

            if (isset($response->statusCode) && $response->statusCode === 201) {
                $paypal_order = $response->result;

                $order = new OdinOrder([
                    'fingerprint' => $fingerprint,
                    'currency' => !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency,
                    'exchange_rate' => $is_currency_supported ? $priceData['exchange_rate'] : 1, // 1 - USD to USD exchange rate
                    'total_paid' => 0,
                    'total_paid_usd' => 0,
                    'txns_fee_usd' => 0,
                    'total_price' => !$is_currency_supported ? $total_price_usd : $total_local_price,
                    'total_price_usd' => $total_price_usd,
                    'customer_phone' => null,
                    'language' => app()->getLocale(),
                    'ip' => $request->ip(),
                    'type' => OrderService::getOrderTypeByProduct($product),
                    'warehouse_id' => $product->warehouse_id,
                    'products' => [],
                    'txns' => [],
                    'page_checkout' => $request->page_checkout,
                    'offer' => AffiliateService::getAttributeByPriority($params['offer_id'] ?? null, $params['offerid'] ?? null),
                    'affiliate' => AffiliateService::validateAffiliateID($affId) ? $affId : null,
                    'txid' => AffiliateService::getValidTxid($params['txid'] ?? null),
                    'shop_currency' => $shop_currency_code,
                    'params' => $params,
                    'ipqualityscore' => $ipqs
                ]);

                PaymentService::addTxnToOrder(
                    $order,
                    [
                        'hash' => $paypal_order->id,
                        'value' => (float)$paypal_order->purchase_units[0]->amount->value,
                        'currency' => $paypal_order->purchase_units[0]->amount->currency_code,
                        'status' => Txn::STATUS_AUTHORIZED,
                        'provider_data' => $paypal_order,
                        'payment_provider' => $payment_api->payment_provider,
                        'payment_api_id' => (string)$payment_api->_id
                    ],
                    ['payment_method' => PaymentMethods::INSTANT_TRANSFER]
                );

                $order->addProduct([
                    'txn_hash' => $paypal_order->id,
                    'sku_code' => $request->sku_code,
                    'quantity' => (int)$request->sku_quantity,
                    'price' => $is_currency_supported ? $local_price : $price_usd,
                    'price_usd' => $price_usd,
                    'warranty_price' => $is_currency_supported ? ($local_warranty_price ?? null) : ($local_warranty_usd ?? null),
                    'warranty_price_usd' => $local_warranty_usd ?? null,
                    'is_main' => true,
                    'is_paid' => false,
                    'is_exported' => false,
                    'is_plus_one' => false,
                    'price_set' => $product->prices['price_set'],
                    'is_upsells' => false
                ], true);

                $order_response = $this->orderService->addOdinOrder($order->attributesToArray(), true);

                $order = $order_response['order'];

                abort_if(!$order_response['success'], 404);
            }
        }
        return [
            'braintree_response' => $response,
            'odin_order_id' => optional($order)->getIdAttribute(),
            'order_currency' => !empty($order->currency) ? $order->currency : '',
            'order_number' => optional($order)->number,
        ];
    }

    /**
     * Verify order
     *
     * @param PayPalVerfifyOrderRequest $request
     * @return array
     * @throws \App\Exceptions\OrderNotFoundException
     */
    public function verifyOrder(PayPalVerfifyOrderRequest $request)
    {
        $response = null;
        try {
            $response = $this->payPalHttpClient->execute(new OrdersCaptureRequest($request->orderID));
        } catch (HttpException $e) {
            logger()->error('Verify PayPal', ['error' => $e->getMessage()]);
            throw new PaymentException('PayPal order verification error');
        }

        if ($response->statusCode < 300) {
            $paypal_order = $response->result;

            $payment_api = PaymentApi::getActivePaypal();

            $order = OdinOrder::getByTxnHash($paypal_order->id, $payment_api->payment_provider); // throwable

            PaymentService::addTxnToOrder(
                $order,
                [
                    'hash' => $paypal_order->id,
                    'capture_hash' => $paypal_order->purchase_units[0]->payments->captures[0]->id  ?? null,
                    'value' => $this->getPayPalOrderValue($paypal_order),
                    'currency' => $this->getPayPalOrderCurrency($paypal_order),
                    'status' => Txn::STATUS_CAPTURED,
                    'provider_data' => $paypal_order,
                    'payment_provider' => $payment_api->payment_provider,
                    'payment_api_id' => (string)$payment_api->_id,
                    'payer_id' => optional($paypal_order->payer)->payer_id
                ],
                ['payment_method' => PaymentMethods::INSTANT_TRANSFER]
            );

            $this->setPayer($order, $paypal_order);
            $this->setShipping($order, $paypal_order);
            $this->saveCustomer($order, $paypal_order);

            // check is flagged
            $main_product = $order->getMainProduct(false);
            if (!empty($main_product) && $main_product['txn_hash'] === $paypal_order->id) {
                $order->is_flagged = $this->isPayPalOrderFlagged($paypal_order);
            }

            $order->status = PaymentService::getOrderStatus($order);

            $order->save();
            if ($order) {
                return ['order_id' => $order->id];
            } else {
                logger()->error(
                    '***VerifyOrder2.Cant find matching order for txh.hash: ' . $paypal_order->id,
                    ['paypal_order' => $paypal_order]
                );
                abort(404);
            }
        }
        logger()->error(
            'Paypal capture request error',
            ['response' => $response]
        );
        abort(404);
    }

    /**
     * @param Request $request
     * @throws \App\Exceptions\OrderNotFoundException
     */
    public function webhooks(Request $request)
    {
        if ($request->input('event_type', '') === 'PAYMENT.CAPTURE.COMPLETED') {
            $payment_api = PaymentApi::getActivePaypal();
            $link = collect($request->input('resource.links'))->filter(function ($link) {
                return Str::contains($link['href'], '/orders/');
            })->first();

            $paypal_order_id = preg_split('/orders\//', $link['href'])[1];

            // Should prevent duplicated calls
            $order = OdinOrder::where(['txns.hash' => $paypal_order_id])->first();

            // If order not found return 2xx code so PP wont retry
            if (!$order) {
                return response(null, 204);
            }

            foreach($order->txns as $transaction) {
                if ($transaction['hash'] === $paypal_order_id && $transaction['status'] === Txn::STATUS_APPROVED) {
                    logger()->info('TXN with # ' . $paypal_order_id . ' was already approved');

                    return response(null, 200);
                }
            }

            $response = $this->payPalHttpClient->execute(new OrdersGetRequest($paypal_order_id));

            if ($response->statusCode === 200 && $response->result->status === self::STATUS_COMPLETED) {
                $paypal_order = $response->result;

                $order = OdinOrder::getByTxnHash($paypal_order->id, $payment_api->payment_provider); // throwable

                PaymentService::addTxnToOrder(
                    $order,
                    [
                        'hash' => $paypal_order->id,
                        'capture_hash' => $paypal_order->purchase_units[0]->payments->captures[0]->id  ?? null,
                        'value' => $this->getPayPalOrderValue($paypal_order),
                        'currency' => $this->getPayPalOrderCurrency($paypal_order),
                        'status' => Txn::STATUS_APPROVED,
                        'provider_data' => $paypal_order,
                        'payment_provider' => $payment_api->payment_provider,
                        'payment_api_id' => (string)$payment_api->_id,
                        'payer_id' => optional($paypal_order->payer)->payer_id
                    ],
                    ['payment_method' => PaymentMethods::INSTANT_TRANSFER]
                );

                // Set is_paid for order products of captured transaction
                $products = $order->getProductsByTxnHash($paypal_order->id);
                if (empty($products)) {
                    $products = $order->getProductsByTxnValue($this->getPayPalOrderValue($paypal_order));
                }

                $is_order_need_to_check = false;
                foreach ($products as $product) {
                    if ($product['is_paid']) {
                        $is_order_need_to_check = true;
                    }
                    $product['is_paid'] = true;
                    $product['txn_hash'] = $paypal_order->id;
                    $order->addProduct($product);
                }

                // reset flagged
                $main_product = $order->getMainProduct(false);
                if (!empty($main_product) && $main_product['txn_hash'] === $paypal_order->id) {
                    $order->is_flagged = false;
                }

                $order = OrderService::calcTotalPaid($order);
                $order->status = PaymentService::getOrderStatus($order, $is_order_need_to_check);
                $order->is_invoice_sent = false;
                $order->save();

                return response(null, 200);
            }
        }
    }

    /**
     * @param OdinOrder $order
     */
    public function calculateTotalPaid(OdinOrder $order)
    {
        $total_pad = 0;
        foreach ($order->products as $product) {
            if ($product['is_txn_approved']) {
                $total_pad += $product['txn_value'];
            }
        }
        $order->total_paid = $total_pad;
    }

    /**
     * @param $paypal_order
     * @return float|int
     */
    private function getPayPalOrderValue($paypal_order)
    {
        $value = 0;
        foreach ($paypal_order->purchase_units as $unit) {
            if ($unit->payments) {
                foreach ($unit->payments->captures as $capture) {
                    $value += (float)$capture->amount->value;
                }
            }
        }
        return $value;
    }

    /**
     * Checks is order flagged
     * @param $paypal_order
     * @return bool
     */
    private function isPayPalOrderFlagged($paypal_order): bool
    {
        return $paypal_order->purchase_units[0]->payments->captures[0]->status === self::STATUS_PENDING;
    }

    /**
     * @param $paypal_order
     * @return mixed
     */
    private function getPayPalOrderCurrency($paypal_order)
    {
        return $paypal_order->purchase_units[0]->payments->captures[0]->amount->currency_code;
    }

    /**
     * Set payper info to order
     *
     * @param $order
     * @param $paypal_order
     */
    private function setPayer($order, $paypal_order)
    {
        if (isset($paypal_order->payer)) {
            $order->customer_email = optional($paypal_order->payer)->email_address;
            if (isset($paypal_order->payer->name)) {
                $order->customer_first_name = optional($paypal_order->payer->name)->given_name;
                $order->customer_last_name = optional($paypal_order->payer->name)->surname;
                if(isset($paypal_order->payer->phone)) {
                    $order->customer_phone = $paypal_order->payer->phone->phone_number->national_number;
                }
            }
        }
    }

    /**
     * Set shipping info to order
     *
     * @param $order
     * @param $paypal_order
     */
    private function setShipping($order, $paypal_order)
    {
        if (!empty($paypal_order->purchase_units) && $paypal_order->purchase_units[0]) {
            $shipping = $paypal_order->purchase_units[0]->shipping ?? null;
            if (!empty($shipping->address)) {
                $shipping_country_code = optional($shipping->address)->country_code;
                // replace c2 to ch(china)
                if ($shipping_country_code == 'c2') {
                    $shipping_country_code = 'cn';
                }
                $order->shipping_country = $shipping_country_code;
                $order->shipping_zip = optional($shipping->address)->postal_code;
                $order->shipping_state = optional($shipping->address)->admin_area_1;
                $order->shipping_city = optional($shipping->address)->admin_area_2;
                $order->shipping_street = optional($shipping->address)->address_line_1;
                $order->shipping_street2 = optional($shipping->address)->address_line_2;
            }
        }
    }

    /**
     * Check ability to add new products into order
     *
     * @param $order
     */
    private function checkIforderAllowedForAddingProducts($order)
    {
        if (!in_array($order->status, [
            OdinOrder::STATUS_NEW,
            OdinOrder::STATUS_PAID,
            OdinOrder::STATUS_HALFPAID,
        ])) {
            logger()->error('Trying to add product to order with status: ' . $order->status, ['order' => $order]);
            abort(404);
        }
    }

    /**
     * Save customer
     *
     * @param $order
     * @param $paypal_order
     */
    private function saveCustomer($order, $paypal_order)
    {
        (new CustomerService)->addOrUpdate([
            'email'         => $order->customer_email,
            'first_name'    => $order->customer_first_name,
            'last_name'     => $order->customer_last_name,
            'country'       => $order->shipping_country,
            'zip'           => $order->shipping_zip,
            'state'         => $order->shipping_state,
            'city'          => $order->shipping_city,
            'street'        => $order->shipping_street,
            'street2'       => $order->shipping_street2,
            'apt'           => $order->shipping_apt,
            'building'      => $order->shipping_building,
            'language'      => $order->language,
            'phone'         => $order->customer_phone,
            'paypal_payer_id' => optional($paypal_order->payer)->payer_id,
            'f' => $order->fingerprint ?? null,
            'page' => $order->page_checkout ?? null,
            'sku' => $order->getMainSku()
        ]);
    }

    /**
     * Get price
     *
     * @param Request $request
     * @param OdinProduct $product
     * @param OdinOrder|null $order
     * @return array
     */
    private function getPrice(Request $request, $product, OdinOrder $order = null)
    {
        // If it's an upsell - calculate total upsell price.
        if ($request->input('order', null) && $request->get('upsells')) {
            abort_if(!$order, 404);
            $upsells_array = $request->get('upsells');

            $upsells_price_data = (new ProductService())->calculateUpsellsTotal($product, $upsells_array, true, $order->currency);

            return [
                'price' => $upsells_price_data['value'],
                'code' => $upsells_price_data['code'],
                'exchange_rate' => $upsells_price_data['exchange_rate'],
            ];
        } else {
            return [
                'price' => $product->prices[$request->sku_quantity]['value'],
                'code' =>  $product->prices['currency'],
                'exchange_rate' => $product->prices['exchange_rate'],
                'warranty' => $product->prices[$request->sku_quantity]['warranty_price'],
            ];
        }
        abort(404);
    }

    /**
     * Find product by SKU
     *
     * @param string $sku
     * @return mixed
     */
    private function findProductBySku(string $sku)
    {
        return OdinProduct::where('skus.code', $sku)->firstOrFail();
    }

    /**
     * Find product by cop id
     * @param string $copId
     * @return type
     */
    private function findProductByCopId(string $copId)
    {
       return OdinProduct::where('prices.price_set', $copId)->first();
    }

    /**
     * Handles Braintree PP exception
     *
     * @param HttpException $exception
     * @param Currency $currency
     * @throws PPCurrencyNotSupportedException
     */
    private function handlePPBraintreeException(HttpException $exception, Currency $currency) {
        $decoded_message = json_decode($exception->getMessage(), true);
        if (!empty($decoded_message['details'][0]['issue']) && $decoded_message['details'][0]['issue'] === 'CURRENCY_NOT_SUPPORTED') {
            $country = UtilsService::$countryCodes[$currency->countryCode];
            $message = [
                'phrase' => 'paypal.error.currency_not_supported',
                'args' => [
                    'currency' => $currency->name,
                    'country' => $country
                ],
            ];

            throw new PPCurrencyNotSupportedException(json_encode($message), $exception->getCode());
        }
    }
}
