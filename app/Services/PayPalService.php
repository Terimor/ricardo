<?php

namespace App\Services;

use App\Http\Requests\PayPalCrateOrderRequest;
use App\Http\Requests\PayPalVerfifyOrderRequest;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Models\Txn;
use BraintreeHttp\HttpResponse;
use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

/**
 * Class PayPalService
 * @package App\Services
 */
class PayPalService
{
    const PROVIDER = 'PayPal';

    const METHOD = 'PayPal';

    const DEFAULT_CURRENCY = 'USD';

    const PAYPAL_ORDER_APPROVED_STATUS = 'APPROVED';

    /**
     * @var PayPalHttpClient
     */
    protected $payPalHttpClient;

    /**
     * @var OrderService
     */
    protected $orderService;

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
        $price = $priceData['price'] * $priceData['exchange_rate'];
        $local_currency = $priceData['code'];
        $local_price = $priceData['price'];
        $total_price = $price;
        $total_local_price = $local_price;
        $items = [[
            'name' => $product->product_name,
            'description' => $product->description,
            'sku' => $request->sku_code,
            'unit_amount' => [
                'currency_code' => $local_currency,
                'value' => $local_price,
            ],
            'quantity' => 1
        ]];
        if ($request->input('is_warrantry_checked') && $product->warranty_percent) {
            $warrantry_price_data = CurrencyService::getLocalPriceFromUsd(($product->warranty_percent / 100) * $price);
            $warrantry_price = $warrantry_price_data['price'] * $warrantry_price_data['exchange_rate'];
            $local_warranty_price = $warrantry_price_data['price'];
            $total_price += $warrantry_price;
            $total_local_price += $local_warranty_price;
            $items[] = [
                'name' => 'Warrantry',
                'description' => 'Warrantry',
                'unit_amount' => [
                    'currency_code' => $local_currency,
                    'value' => $local_warranty_price,
                ],
                'quantity' => 1
            ];
        }

        $unit = [
            'description' => $product->description,
            'amount' => [
                'currency_code' => $local_currency,
                'value' => $total_local_price,
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
                'payment_method' => self::METHOD,
                'payment_provider' => self::PROVIDER,
            ]);
            abort_if(!$txn_response['success'], 404);
            $txn = $txn_response['txn'];

            $odin_order_product = [
                'sku_code' => $request->sku_code,
                'quantity' => $request->sku_quantity,
                'price' => $local_price,
                'price_usd' => $price,
                'warranty_price' => $local_warranty_price ?? null,
                'warranty_price_usd' => $warrantry_price ?? null,
                'is_main' => !$upsell_order,
                'txn_hash' => $txn['hash'],
                'txn_value' => $txn['value'],
                'txn_approved' => false,
                'txn_charged_back' => false,
            ];

            if ($upsell_order) {
                $upsell_order->products = array_merge($upsell_order->products, [$odin_order_product]);
                $upsell_order->status = $this->getOrderStatus($upsell_order);
                $upsell_order->total_price += $local_price;
                $upsell_order->total_price_usd += $price;
                $upsell_order->save();
            } else {
                $order_reponse = $this->orderService->addOdinOrder([
                    'currency' => $local_currency,
                    'exchange_rate' => $priceData['exchange_rate'],
                    'total_paid' => 0,
                    'total_price' => $total_local_price,
                    'total_price_usd' => $total_price,
                    'payment_provider' => self::PROVIDER,
                    'payment_method' => self::METHOD,
                    'customer_phone' => null,
                    'language' => app()->getLocale(),
                    'ip' => $request->ip(),
                    'warehouse_id' => $product->warehouse_id,
                    'products' => [$odin_order_product],
                    'page_checkout' => $request->page_checkout,
                    'offer' => $request->offer,
                    'affiliate' => $request->affiliate,
                ]);

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
        $response = $this->payPalHttpClient->execute(new OrdersGetRequest($request->orderID));
        $paypal_order = $response->result;
        $txn_response = $this->orderService->addTxn([
            'hash' => $paypal_order->id,
            'value' => (float)$paypal_order->purchase_units[0]->amount->value,
            'currency' => $paypal_order->purchase_units[0]->amount->currency_code,
            'provider_data' => $paypal_order,
            'payment_method' => self::METHOD,
            'payment_provider' => self::PROVIDER,
        ]);
        $txn = $txn_response['txn'];
        $order = OdinOrder::where('products.txn_hash', $txn['hash'])->first();
        $this->checkIforderAllowedForAddingProducts($order);

        if ($order) {
            $this->setPayer($order, $paypal_order);
            $this->setShipping($order, $paypal_order);
            $this->saveCustomer($order);
            $product_key = collect($order->products)->search(function ($product) use($txn) {
                return $product['txn_hash'] === $txn['hash'];
            });
            $products = $order->products;
            switch ($paypal_order->status) {
                case self::PAYPAL_ORDER_APPROVED_STATUS:
                    $products[$product_key]['txn_approved'] = true;
                    $order->products = $products;
                    $order->total_paid += (float)$txn['value'];
                    break;
            }
            $order->status = $this->getOrderStatus($order);
            $order->save();

            return [
                'order_id' => $order->_id
            ];
        }

        logger()->error(
            'Cant find matching order for txh.hash: ' . $paypal_order->id,
            ['paypal_order' => $paypal_order]
        );
        abort(404);
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
            $order->payer_id = optional($paypal_order->payer)->payer_id;
            $order->customer_email = optional($paypal_order->payer)->email_address;
            if (isset($paypal_order->payer->name)) {
                $order->customer_first_name = optional($paypal_order->payer->name)->given_name;
                $order->customer_last_name = optional($paypal_order->payer->name)->surname;
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
            case ($order->total_paid === $order->total_price):
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
                    $price = ($upsell['discount_percent'] / 100) * $product->prices[$request->sku_quantity]['value'];
                    return CurrencyService::getLocalPriceFromUsd($price);
                }
            }
        } else {
            return $product->prices[$request->sku_quantity]['local'];
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
