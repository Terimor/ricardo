<?php

namespace App\Services;

use App\Exceptions\PPCurrencyNotSupportedException;
use App\Http\Requests\PayPalCrateOrderRequest;
use App\Http\Requests\PayPalVerfifyOrderRequest;
use App\Models\Currency;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Models\Txn;
use BraintreeHttp\HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

/**
 * Class PayPalService
 * @package App\Services
 */
class PayPalService
{
    const DEFAULT_CURRENCY = 'USD';

    const PAYPAL_ORDER_COMPLETED_STATUS = 'COMPLETED';

    /**
     * @var PayPalHttpClient
     */
    protected $payPalHttpClient;

    /**
     * @var CustomerService
     */
    protected $customerService;

    /**
     * @var OrderService
     */
    protected $orderService;

    public static $supported_currencies = [
            'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD',
            'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'
        ];

    /**
     * PayPalService constructor.
     * @param PayPalHttpClient $payPalHttpClient
     * @param CustomerService $customerService
     * @param OrderService $orderService
     */
    public function __construct(PayPalHttpClient $payPalHttpClient, CustomerService $customerService, OrderService $orderService)
    {
        $this->payPalHttpClient = $payPalHttpClient;
        $this->customerService = $customerService;
        $this->orderService = $orderService;
    }


    public function createUpsellOrder(PayPalCrateOrderRequest $request): array
    {
        $upsell_order = OdinOrder::find($request->get('order'));
        if (!$upsell_order) {
            abort(404);
        }

        // If no upsells selected
        if(!$request->get('upsells')) {
            return [
                'braintree_response' => null,
                'odin_order_id' => optional($upsell_order)->getIdAttribute(),
                'order_currency' => $upsell_order->currency
            ];
        }

        $this->checkIforderAllowedForAddingProducts($upsell_order);
        $product = $this->findProductBySku($request->get('sku_code'));

        $total_upsell_price = $total_upsell_price_usd = 0;
        $upsell_order_exchange_rate = null;
        $pp_items = $upsell_order_products = [];

        foreach ($request->get('upsells') as $upsell_product_id => $upsell_product_quantity) {
            $temp_upsell_product = (new ProductService())->getUpsellProductById($product, $upsell_product_id, $upsell_product_quantity, $upsell_order->currency);
            $temp_upsell_item_price = $temp_upsell_product['upsellPrices'][$upsell_product_quantity]['price'];
            $upsell_order_exchange_rate = $temp_upsell_product['upsellPrices'][$upsell_product_quantity]['exchange_rate'];

            // Converting to USD.
            $temp_upsell_item_usd_price = CurrencyService::roundValueByCurrencyRules(
                $temp_upsell_item_price / $upsell_order_exchange_rate,
                self::DEFAULT_CURRENCY);

            $total_upsell_price += $temp_upsell_item_price;
            $total_upsell_price_usd += $temp_upsell_item_usd_price;

            // Setting PayPal order items
            $pp_items[] = [
                'name' => $temp_upsell_product->product_name,
                'description' => $temp_upsell_product->long_name,
                'unit_amount' => [
                    'currency_code' => $upsell_order->currency,
                    'value' => $temp_upsell_item_price,
                ],
                'quantity' => $upsell_product_quantity
            ];

            // Creating array of an order upsell products.
            $upsell_order_products[] = [
                'sku_code' => $temp_upsell_product['upsell_sku'],
                'quantity' => (int)$upsell_product_quantity,
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
            ],
            'items' => $pp_items,            
        ];

        $pp_request = new OrdersCreateRequest();
        $pp_request->prefer('return=representation');
        $pp_request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [$pp_purchase_unit]
        ];

        try {
            $response = $this->payPalHttpClient->execute($pp_request);
        } catch (HttpException $e) {
            $shop_currency = CurrencyService::getCurrency($upsell_order->currency);
            $this->handlePPBraintreeException($e, $shop_currency);
        }

        // If success create new txn and update order
        if ($response->statusCode === 201) {
            $paypal_order = $response->result;

            // Creating a capture transaction
            $txn_response = $this->orderService->addTxn([
                'hash' => $paypal_order->id,
                'value' => (float)$paypal_order->purchase_units[0]->amount->value,
                'currency' => $paypal_order->purchase_units[0]->amount->currency_code,
                'provider_data' => $paypal_order,
                'payment_method' => PaymentService::METHOD_INSTANT_TRANSFER,
                'payment_provider' => PaymentService::PROVIDER_PAYPAL,
                'payer_id' => '',
            ], true);

            $txn_attributes = $txn_response['txn']->attributesToArray();

            $order_txn_data = [
                'hash' => $txn_response['txn']->hash,
                'value' => $txn_response['txn']->value,
                'status' =>  Txn::STATUS_CAPTURED,
                'is_charged_back' => false,
                'fee' => null,
                'payment_provider' => $txn_response['txn']->payment_provider,
                'payment_method' => $txn_response['txn']->payment_method,
                'payer_id' => $txn_response['txn']->payer_id,
            ];

            $txns = array_filter($upsell_order['txns'], function($item) use ($txn_response) {
                return $item['hash'] !== $txn_response['txn']->hash;
            });

            // Setting txn_hash for an upsell products
            $upsell_order_products = collect($upsell_order_products)
                ->transform(function($item, $key) use ($txn_attributes, $product) {
                    $item['txn_hash'] = $txn_attributes['hash'];
                    $item['is_plus_one'] = optional($this->findProductBySku($item['sku_code']))->_id === $product->_id;

                    return $item;
                })->toArray();

            // Update main order
            $upsell_order->total_price += $total_upsell_price;
            $upsell_order->total_price_usd += CurrencyService::roundValueByCurrencyRules(
                $total_upsell_price / $upsell_order_exchange_rate,
                self::DEFAULT_CURRENCY);
            $upsell_order->status = $this->getOrderStatus($upsell_order);
            $upsell_order->txns = array_merge($txns, [$order_txn_data]);
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
     */
    public function createOrder(PayPalCrateOrderRequest $request): array
    {
        $upsell_order = $request->get('order') ? OdinOrder::find($request->get('order')) : null;

        if ($upsell_order) {
            return $this->createUpsellOrder($request);
        }
        $order = $upsell_order;
        $product = $this->findProductBySku($request->sku_code);
        $priceData = $this->getPrice($request, $product);
        $price = round($priceData['price'] / $priceData['exchange_rate'], 2);

        // Currency of the prices show on the shop page
        $shop_currency = CurrencyService::getCurrency();
        $shop_currency_code = $shop_currency->code;

        $local_currency = $priceData['code'];
        $local_price = $priceData['price'];
        $total_price = $price;
        $total_local_price = $local_price;

        // If local currency is not supported by PayPal convert to USD. Used for purchase only.
        $is_currency_supported = in_array($priceData['code'], self::$supported_currencies);

        $pp_currency_code = !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency;

        $items = [[
            'name' => $product->product_name,
            'description' => $product->long_name,
            'sku' => $request->sku_code,
            'unit_amount' => [
                'currency_code' => $pp_currency_code,
                'value' => !$is_currency_supported ? $price : $local_price,
            ],
            'quantity' => 1
        ]];
        if ($request->input('is_warranty_checked') && $product->warranty_percent && !$upsell_order) {
            $local_warranty_price = CurrencyService::roundValueByCurrencyRules($priceData['warranty'], $priceData['code']);
            $local_warranty_usd = CurrencyService::roundValueByCurrencyRules(
                CurrencyService::calculateWarrantyPrice((float)$product->warranty_percent, $total_price),
                self::DEFAULT_CURRENCY
            );
            $total_price += $local_warranty_usd;
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
            'description' => $product->long_name,
            'amount' => [
                'currency_code' => $pp_currency_code,
                'value' => !$is_currency_supported ? $total_price : $total_local_price,
                'items' => $items,
            ]
        ];

        $pp_request = new OrdersCreateRequest();
        $pp_request->prefer('return=representation');
        $pp_request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [$unit]
        ];

        try {
            $response = $this->payPalHttpClient->execute($pp_request);
        } catch (HttpException $e) {
            $this->handlePPBraintreeException($e, $shop_currency);
        }

        if ($response->statusCode === 201) {
            $paypal_order = $response->result;

            $txn_response = $this->orderService->addTxn([
                'hash' => $paypal_order->id,
                'value' => (float)$paypal_order->purchase_units[0]->amount->value,
                'currency' => $paypal_order->purchase_units[0]->amount->currency_code,
                'provider_data' => $paypal_order,
                'payment_method' => PaymentService::METHOD_INSTANT_TRANSFER,
                'payment_provider' => PaymentService::PROVIDER_PAYPAL,
                'payer_id' => '',
            ], true);
            abort_if(!$txn_response['success'], 404);

            $txn = $txn_response['txn']->attributesToArray();

            $odin_order_product = [
                'sku_code' => $request->sku_code,
                'quantity' => (int)$request->sku_quantity,
                'price' => $is_currency_supported ? $local_price : $price,
                'price_usd' => $price,
                'warranty_price' => $is_currency_supported ? ($local_warranty_price ?? null) : ($local_warranty_usd ?? null),
                'warranty_price_usd' => $local_warranty_usd ?? null,
                'is_main' => !$upsell_order,
                'is_paid' => false,
                'is_exported' => false,
                'is_plus_one' => false,
                'price_set' => $product->prices['price_set'],
                'txn_hash' => $txn['hash'],
                'is_upsells' => (bool)$upsell_order,
            ];

            if ($upsell_order) {
                /** Check product "is_plus_one" if this order has main product with the same ID  */
                $odin_order_product['is_plus_one'] = collect($upsell_order->products)->search(function ($item) use ($product) {
                        return $item['is_main'] && optional($this->findProductBySku($item['sku_code']))->_id === $product->_id;
                    }) !== false;
                $upsell_order->products = array_merge($upsell_order->products, [$odin_order_product]);
                $upsell_order->status = $this->getOrderStatus($upsell_order);
                $upsell_order->total_price += $is_currency_supported ? $local_price : $price;
                $upsell_order->total_price_usd += $price;
                $upsell_order->save();
            } else {
                $order_txn_data = [
                    'hash' => $txn_response['txn']->hash,
                    'value' => $txn_response['txn']->value,
                    'status' =>  Txn::STATUS_CAPTURED,
                    'is_charged_back' => false,
                    'fee' => null,
                    'payment_provider' => $txn_response['txn']->payment_provider,
                    'payment_method' => $txn_response['txn']->payment_method,
                    'payer_id' => $txn_response['txn']->payer_id,
                ];
                $params = !empty($request->page_checkout) ? \Utils::getParamsFromUrl($request->page_checkout) : null;

                $order_reponse = $this->orderService->addOdinOrder([
                    'currency' => !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency,
                    'exchange_rate' => $is_currency_supported ? $priceData['exchange_rate'] : 1, // 1 - USD to USD exchange rate
                    'total_paid' => 0,
                    'total_paid_usd' => 0,
                    'total_price' => !$is_currency_supported ? $total_price : $total_local_price,
                    'total_price_usd' => $total_price,
                    'customer_phone' => null,
                    'language' => app()->getLocale(),
                    'ip' => $request->ip(),
                    'warehouse_id' => $product->warehouse_id,
                    'products' => [$odin_order_product],
                    'txns' => [$order_txn_data],
                    'page_checkout' => $request->page_checkout,
                    'offer' => !empty($params['offer_id']) ? $params['offer_id'] : null,
                    'affiliate' => !empty($params['aff_id']) ? $params['aff_id'] : null,
                    'shop_currency' => $shop_currency_code,
                    'params' => $params,
                    'ipqualityscore' => $request->get('ipqs')
                ], true);

                $order = $order_reponse['order'];

                abort_if(!$order_reponse['success'], 404);
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
     */
    public function verifyOrder(PayPalVerfifyOrderRequest $request)
    {
        $response = $this->payPalHttpClient->execute(new OrdersCaptureRequest($request->orderID));
        if ($response->statusCode < 300) {
            $paypal_order = $response->result;

            $paypal_order_value = $this->getPayPalOrderValue($paypal_order);
            $paypal_order_currency = $this->getPayPalOrderCurrency($paypal_order);

            $txn_response = $this->orderService->addTxn([
                'hash' => $paypal_order->id,
                'value' => $paypal_order_value,
                'currency' => $paypal_order_currency,
                'provider_data' => $paypal_order,
                'payment_method' => PaymentService::METHOD_INSTANT_TRANSFER,
                'payment_provider' => PaymentService::PROVIDER_PAYPAL,
                'payer_id' => optional($paypal_order->payer)->payer_id,
            ], true);

            $txn = $txn_response['txn']->attributesToArray();

            $order = OdinOrder::where('products.txn_hash', $paypal_order->id)->first();

            $order_txn_data = [
                'hash' => $txn_response['txn']->hash,
                'value' => $txn_response['txn']->value,
                'status' => Txn::STATUS_CAPTURED,
                'is_charged_back' => false,
                'fee' => null,
                'payment_provider' => $txn_response['txn']->payment_provider,
                'payment_method' => $txn_response['txn']->payment_method,
                'payer_id' => $txn_response['txn']->payer_id,
            ];

            $txns = array_filter($order['txns'], function($item) use ($txn_response) {
                return $item['hash'] !== $txn_response['txn']->hash;
            });
            $order->txns = array_merge($txns, [$order_txn_data]);

            $this->setPayer($order, $paypal_order);
            $this->setShipping($order, $paypal_order);
            $this->saveCustomer($order, $paypal_order);

            $product_key = collect($order->products)->search(function ($product) use ($txn) {
                return $product['txn_hash'] === $txn['hash'];
            });

            $products = $order->products;

            $order->products = $products;
            $order->status = $this->getOrderStatus($order);

            $order->save();
            if ($order) {
                return ['order_id' => $order->id];
            } else {
                logger()->error(
                    'Cant find matching order for txh.hash: ' . $paypal_order->id,
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
     */
    public function webhooks(Request $request)
    {
        if ($request->input('event_type', '') === 'PAYMENT.CAPTURE.COMPLETED') {
            $link = collect($request->input('resource.links'))->filter(function ($link) {
                return Str::contains($link['href'], '/orders/');
            })->first();
            $fee = $request->resource['seller_receivable_breakdown']['paypal_fee']['value'] ?? 0;

            if (!$fee) {
                logger()->error("Wrong PayPal fee: " . json_encode($request->resource));
            }

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

            $response = $this->payPalHttpClient->execute(
                new OrdersGetRequest(
                    $paypal_order_id
                )
            );

            if ($response->statusCode === 200 && $response->result->status === self::PAYPAL_ORDER_COMPLETED_STATUS) {
                $paypal_order = $response->result;
                $paypal_order_value = $this->getPayPalOrderValue($paypal_order);
                $paypal_order_currency = $this->getPayPalOrderCurrency($paypal_order);
                $txn_response = $this->orderService->addTxn([
                    'hash' => $paypal_order->id,
                    'value' => $paypal_order_value,
                    'currency' => $paypal_order_currency,
                    'provider_data' => $paypal_order,
                    'payment_method' => PaymentService::METHOD_INSTANT_TRANSFER,
                    'payment_provider' => PaymentService::PROVIDER_PAYPAL,
                    'payer_id' => optional($paypal_order->payer)->payer_id,
                ], true);

                $txn = $txn_response['txn']->attributesToArray();

                $order = OdinOrder::where('products.txn_hash', $txn['hash'])->first();

                $order_txn_data = [
                    'hash' => $txn_response['txn']->hash,
                    'value' => $txn_response['txn']->value,
                    'status' => Txn::STATUS_APPROVED,
                    'is_charged_back' => false,
                    'fee' => (float)$fee,
                    'payment_provider' => $txn_response['txn']->payment_provider,
                    'payment_method' => $txn_response['txn']->payment_method,
                    'payer_id' => $txn_response['txn']->payer_id,
                ];

                $txns = array_filter($order['txns'], function($item) use ($txn_response) {
                    return $item['hash'] !== $txn_response['txn']->hash;
                });
                $order->txns = array_merge($txns, [$order_txn_data]);

                // Set is_paid for order products of captured transaction
                $temp_txn_products_prices = 0;
                $order->products = collect($order->products)
                    ->map(function($item, $key) use (&$temp_txn_products_prices, $txn) {
                        if ($item['txn_hash'] == $txn['hash']) {
                            $temp_txn_products_prices+= $item['price'];
                            $item['is_paid'] = true;
                        }

                        return $item;
                    })
                    ->toArray();

                $order->total_paid+= $paypal_order_value;

                // Setting total_paid_usd value
                $pp_total_paid_usd = $paypal_order_value;
                if ($paypal_order_currency !== self::DEFAULT_CURRENCY) {
                    $pp_order_currency_rate = CurrencyService::getCurrency($paypal_order_currency)->usd_rate;
                    $pp_total_paid_usd = CurrencyService::roundValueByCurrencyRules($paypal_order_value / $pp_order_currency_rate, self::DEFAULT_CURRENCY);
                }
                $order->total_paid_usd += $pp_total_paid_usd;

                $currency = CurrencyService::getCurrency($order->currency);
                $order->txns_fee_usd += CurrencyService::roundValueByCurrencyRules($fee / $currency->usd_rate, self::DEFAULT_CURRENCY);

                $order->status = $this->getOrderStatus($order);
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
     * @param OdinOrder $order
     */
    public function calculateTotalFee(OdinOrder $order)
    {
        $total_fee = 0;
        foreach ($order->products as $product) {
            if ($product['is_txn_approved']) {
                $total_fee += $product['txn_fee'];
            }
        }
        $currency = CurrencyService::getCurrency($order->currency);
        $order->txns_fee_usd = CurrencyService::roundValueByCurrencyRules($total_fee / $currency->usd_rate, self::DEFAULT_CURRENCY);
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
     * @param $paypal_order
     * @return mixed
     */
    private function getPayPalOrderCurrency($paypal_order)
    {
        return $paypal_order->purchase_units[0]->payments->captures[0]->amount->currency_code;
    }

    /**
     * Returns PayPal status string from a PayPal order object
     *
     * @param $paypal_order
     * @return string
     */
    private function getPayPalOrderStatus($paypal_order)
    {
        return strtolower($paypal_order->status);
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
                $order->shipping_country = optional($shipping->address)->country_code;
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
     * Get order status
     *
     * @param $order
     * @return string
     */
    private function getOrderStatus($order)
    {
        switch (true) {
            case ($order->total_paid == 0):
                return OdinOrder::STATUS_NEW;
            case (
                number_format($order->total_paid, 2) === number_format($order->total_price, 2)
                && number_format($order->total_paid_usd, 2) === number_format($order->total_price_usd, 2)
            ):
                return OdinOrder::STATUS_PAID;
            default:
                return OdinOrder::STATUS_HALFPAID;
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
        $this->customerService->addOrUpdate([
            'email' => $order->customer_email,
            'first_name' => $order->customer_first_name,
            'last_name' => $order->customer_last_name,
            'country' => $order->shipping_country,
            'zip' => $order->shipping_zip,
            'state' => $order->shipping_state,
            'city' => $order->shipping_city,
            'street' => $order->shipping_street,
            'street2' => $order->shipping_street2,
            'language' => $order->language,
            'paypal_payer_id' => optional($paypal_order->payer)->payer_id
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

            $upsells_price_data = (new ProductService())->calculateUpsellsTotal($product, $upsells_array, null, true, $order->currency);

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
