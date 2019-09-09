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

    /**
     * Creating order
     *
     * @param PayPalCrateOrderRequest $request
     * @return HttpResponse
     */
    public function createOrder(PayPalCrateOrderRequest $request): HttpResponse
    {
        $upsell_order = $request->order_id ? $order = OdinOrder::find($request->order_id) : null;

        if ($upsell_order) {
            $this->checkIforderAllowedForAddingProducts($upsell_order);
        }
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
                'price' => $local_price,
                'price_usd' => $price,
                'warranty_price' => $local_warranty_price ?? null,
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
                $upsell_order->total_price += $local_price;
                $upsell_order->total_price_usd += $price;
                $upsell_order->save();
            } else {
                $order_reponse = $this->orderService->addOdinOrder([
                    'currency' => !$is_currency_supported ? self::DEFAULT_CURRENCY : $local_currency,
                    'exchange_rate' => $priceData['exchange_rate'],
                    'total_paid' => 0,
                    'total_price' => !$is_currency_supported ? $total_price : $total_local_price,
                    'total_price_usd' => $total_price,
                    'customer_phone' => null,
                    'language' => app()->getLocale(),
                    'ip' => $request->ip(),
                    'warehouse_id' => $product->warehouse_id,
                    'products' => [$odin_order_product],
                    'page_checkout' => $request->page_checkout,
                    'offer' => $request->offer,
                    'affiliate' => $request->affiliate,
                    'shop_currency' => $shop_currency_code,
                    'txns' => [$txn]
                ], true);

                $order = $order_reponse['order'];
                $order->addTransaction($txn_response['txn']);

                abort_if(!$order_reponse['success'], 404);
            }
        }
        return $response;
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

            /** @var Txn $txn_object */
            $txn_response = $this->orderService->addTxn([
                'hash' => $paypal_order->id,
                'value' => $paypal_order_value,
                'status' => $this->getPayPalOrderStatus($paypal_order),
                'currency' => $paypal_order_currency,
                'provider_data' => $paypal_order,
                'payment_method' => PaymentService::METHOD_INSTANT_TRANSFER,
                'payment_provider' => PaymentService::PROVIDER_PAYPAL,
                'payer_id' => optional($paypal_order->payer)->payer_id,
            ], true);

            $txn = $txn_response['txn']->attributesToArray();

            $order = OdinOrder::where('products.txn_hash', $paypal_order->id)->first();
            $this->setPayer($order, $paypal_order);
            $this->setShipping($order, $paypal_order);
            $this->saveCustomer($order);

            $product_key = collect($order->products)->search(function ($product) use ($txn) {
                return $product['txn_hash'] === $txn['hash'];
            });

            $products = $order->products;

            if ($product_key !== null) {
                $products[$product_key]['is_txn_captured'] = true;
                $products[$product_key]['txn_value'] = $txn['value'];
            }
            $order->products = $products;
            $order->status = $this->getOrderStatus($order);
            $order->addTransaction($txn_response['txn']);
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
                $order->addTransaction($txn_response['txn']);
                $product_key = collect($order->products)->search(function ($product) use ($txn) {
                    return $product['txn_hash'] === $txn['hash'];
                });
                $products = $order->products;

                if ($product_key !== null) {
                    $products[$product_key]['is_txn_approved'] = true;
                    $products[$product_key]['txn_value'] = $txn['value'];
                    if ($fee) {
                        $products[$product_key]['txn_fee'] = (float)$fee;
                    }
                }
                $order->products = $products;
                $this->calculateTotalPaid($order);
                $this->calculateTotalFee($order);
                $order->status = $this->getOrderStatus($order);
                $order->save();
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
     */
    private function saveCustomer($order)
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
            'street2' => $order->shipping_street2
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
    private function getPrice(Request $request, OdinProduct $product, OdinOrder $order = null)
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
