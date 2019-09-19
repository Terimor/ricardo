<?php

namespace App\Services;

use App\Http\Requests\PayPalCrateOrderRequest;
use App\Http\Requests\PayPalVerfifyOrderRequest;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Models\Txn;
use BraintreeHttp\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use App\Services\PaymentService;
use App\Services\EmailService;

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
     * @param OrderService $orderService
     */
    public function __construct(PayPalHttpClient $payPalHttpClient, OrderService $orderService)
    {
        $this->payPalHttpClient = $payPalHttpClient;
        $this->orderService = $orderService;
    }


    public function createUpsellOrder(PayPalCrateOrderRequest $request): array
    {
        $upsell_order = OdinOrder::find($request->get('order_id'));
        if (!$upsell_order) {
            abort(404);
        }

        $this->checkIforderAllowedForAddingProducts($upsell_order);

        $product = $this->findProductBySku($request->get('sku_code'));
        $upsells_array = $request->get('upsells');
        if (empty($upsells_array)) {
            abort(404);
        }

        $priceData = (new ProductService())->calculateUpsellsTotal($product, $upsells_array, null, true);
        $priceData['price'] = $priceData['value'];
        $is_currency_supported = in_array($priceData['code'], self::$supported_currencies);
        $upsells_total_local_currency = $priceData['code'];
        $upsells_total_price = $priceData['price'];
        $upsells_total_price_usd = $price = round($priceData['price'] / $priceData['exchange_rate'], 2);

        $pp_items = [];
        $upsell_order_products = []; // Will store upsell products that will be added to an existing order products array.
        $upsell_order_currency_code = !$is_currency_supported ? self::DEFAULT_CURRENCY : $upsells_total_local_currency;
        $upsell_order_total_price = !$is_currency_supported ? $upsells_total_price_usd : $upsells_total_price;

        foreach ($request->get('upsells') as $upsell_product_id => $upsell_product_quantity) {
            $temp_upsell_product = (new ProductService())->getUpsellProductById($product, $upsell_product_id);
            $temp_upsell_product_usd_price = round($temp_upsell_product['upsellPrices'][$upsell_product_quantity]['price'] / $temp_upsell_product['upsellPrices'][$upsell_product_quantity]['exchange_rate'], 2);
            $temp_upsell_product_local_price = $temp_upsell_product['upsellPrices'][$upsell_product_quantity]['price'];

            // Adding products to a paypal items list.
            $temp_pp_price = !$is_currency_supported ? $temp_upsell_product_usd_price : $temp_upsell_product_local_price;
            $pp_items[] = [
                'name' => $temp_upsell_product->product_name,
                'description' => $temp_upsell_product->long_name,
                'unit_amount' => [
                    'currency_code' => $upsell_order_currency_code,
                    'value' => $temp_pp_price,
                ],
                'quantity' => $upsell_product_quantity
            ];

            // Creating array of an order upsell products
            $upsell_order_products[] = [
                'sku_code' => $temp_upsell_product['upsell_sku'],
                'quantity' => (int)$upsell_product_quantity,
                'price' => $temp_pp_price,
                'price_usd' => $temp_upsell_product_usd_price,
                'warranty_price' => null,
                'warranty_price_usd' => null,
                'is_main' => false,
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
                'currency_code' => $upsell_order_currency_code,
                'value' => $upsell_order_total_price,
                'items' => $pp_items,
            ]
        ];

        $pp_request = new OrdersCreateRequest();
        $pp_request->prefer('return=representation');
        $pp_request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [$pp_purchase_unit]
        ];

        $response = $this->payPalHttpClient->execute($pp_request);

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

            // Setting txn_hash for an upsell products
            $upsell_order_products = collect($upsell_order_products)->transform(function($item, $key) use ($txn_attributes) {
                $item['txn_hash'] = $txn_attributes['hash'];

                return $item;
            })->toArray();

            // Update main order
            $upsell_order->total_price += $upsell_order_total_price;
            $upsell_order->total_price_usd += $upsells_total_price_usd;
            $upsell_order->status = $this->getOrderStatus($upsell_order);
            $upsell_order->products = array_merge($upsell_order->products, $upsell_order_products);
            $upsell_order->save();
        }

        return [
            'braintree_response' => $response,
            'odin_order_id' => optional($upsell_order)->getIdAttribute()
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
        $upsell_order = $request->order_id ? $order = OdinOrder::find($request->order_id) : null;

        if ($upsell_order) {
            return $this->createUpsellOrder($request);
        }
        $order = $upsell_order;
        $product = $this->findProductBySku($request->sku_code);
        $priceData = $this->getPrice($request, $product, $upsell_order);
        $price = round($priceData['price'] / $priceData['exchange_rate'], 2);

        // Currency of the prices show on the shop page
        $shop_currency_code = CurrencyService::getCurrency()->code;

        $local_currency = $priceData['code'];
        $local_price = $priceData['price'];
        $total_price = $price;
        $total_local_price = $local_price;

        // If local currency is not supported by PayPal convert to USD. Used for purchase only.
        $is_currency_supported = in_array($priceData['code'], self::$supported_currencies);

        $items = [[
            'name' => $product->product_name,
            'description' => $product->long_name,
            'sku' => $request->sku_code,
            'unit_amount' => [
                'currency_code' => !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency,
                'value' => !$is_currency_supported ? $price : $local_price,
            ],
            'quantity' => 1
        ]];
        if ($request->input('is_warranty_checked') && $product->warranty_percent && !$upsell_order) {
            $local_warranty_price = $priceData['warranty'];
            $local_warranty_usd = CurrencyService::calculateWarrantyPrice($product->warranty_percent, $total_price);
            $total_price += $local_warranty_usd;
            $total_local_price += $local_warranty_price;
            $items[] = [
                'name' => 'Warranty',
                'description' => 'Warranty',
                'unit_amount' => [
                    'currency_code' => !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency,
                    'value' => !$is_currency_supported ? $local_warranty_usd : $local_warranty_price,
                ],
                'quantity' => 1
            ];
        }
        $unit = [
            'description' => $product->long_name,
            'amount' => [
                'currency_code' => !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency,
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

        $response = $this->payPalHttpClient->execute($pp_request);

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

                $order_reponse = $this->orderService->addOdinOrder([
                    'currency' => !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency,
                    'exchange_rate' => $is_currency_supported ? $priceData['exchange_rate'] : 1, // 1 - USD to USD exchange rate
                    'total_paid' => 0,
                    'total_price' => !$is_currency_supported ? $total_price : $total_local_price,
                    'total_price_usd' => $total_price,
                    'customer_phone' => null,
                    'language' => app()->getLocale(),
                    'ip' => $request->ip(),
                    'warehouse_id' => $product->warehouse_id,
                    'products' => [$odin_order_product],
                    'txns' => [$order_txn_data],
                    'page_checkout' => $request->page_checkout,
                    'offer' => $request->offer,
                    'affiliate' => $request->affiliate,
                    'shop_currency' => $shop_currency_code,
                    'params' => !empty($request->params) ? $request->params : null
                ], true);

                $order = $order_reponse['order'];

                abort_if(!$order_reponse['success'], 404);
            }
        }
        return [
            'braintree_response' => $response,
            'odin_order_id' => optional($order)->getIdAttribute()
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
                'status' => Txn::STATUS_APPROVED,
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
                // send confirmation email
                (new EmailService())->sendConfirmationEmail($order);
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
            $fee = $request->resource['seller_receivable_breakdown']['paypal_fee']['value'];
            $response = $this->payPalHttpClient->execute(
                new OrdersGetRequest(
                    preg_split('/orders\//', $link['href'])[1]
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
                    'fee' => $fee,
                    'payment_provider' => $txn_response['txn']->payment_provider,
                    'payment_method' => $txn_response['txn']->payment_method,
                    'payer_id' => $txn_response['txn']->payer_id,
                ];

                $txns = array_filter($order['txns'], function($item) use ($txn_response) {
                    return $item['hash'] !== $txn_response['txn']->hash;
                });
                $order->txns = array_merge($txns, [$order_txn_data]);


                // Check if sum of upsell prices = $paypal_order_value
                $temp_upsell_products_prices = 0;
                collect($order->products)
                    ->reject(function ($item, $key) use ($txn) {
                        return $item['txn_hash'] !== $txn['hash'];
                    })
                    ->each(function($item, $key) use (&$temp_upsell_products_prices) {
                        $temp_upsell_products_prices+= $item['price'];
                    });

                // Amount paid !== Sum of prices of transaction items.
                if ($temp_upsell_products_prices !== $paypal_order_value) {
                    logger()->alert('Amount paid for an order: ' . $order->getIdAttribute() . ' in a transaction # ' . $txn_response['txn']->hash . ' differs from a total products price.');
                }

                $order->total_paid+= $paypal_order_value;
                $currency = CurrencyService::getCurrency($order->currency);
                $order->txns_fee_usd += round($fee / $currency->usd_rate, 2);

                $order->status = $this->getOrderStatus($order);
                $order->save();

                // send satisfaction email
                (new EmailService())->sendSatisfactionEmail($order);
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
        $order->txns_fee_usd = round($total_fee / $currency->usd_rate, 2);
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
            case (number_format($order->total_paid, 2) === number_format($order->total_price, 2)):
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
        $this->orderService->addCustomer([
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
        if ($request->input('order_id', null)) {
            abort_if(!$order, 404);
            $main_order_product = collect($order->products)->where('is_main', true)->first();
            $main_order_product = $this->findProductBySku($main_order_product['sku_code']);
            $upsell = collect($main_order_product->upsells)->where('product_id', $product->_id)->first();

            if ($upsell) {
                if ($upsell['fixed_price']) {
                    return CurrencyService::getLocalPriceFromUsd($upsell['fixed_price']);
                } elseif ($upsell['discount_percent']) {
                    $price = ((100 - $upsell['discount_percent']) / 100) * $product->prices[$request->sku_quantity]['value'];
                    return CurrencyService::getLocalPriceFromUsd($price);
                }
            }
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
}
