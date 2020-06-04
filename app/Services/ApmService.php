<?php

namespace App\Services;

use App\Models\Txn;
use App\Models\Domain;
use App\Models\Currency;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Constants\PaymentProviders;
use App\Exceptions\AuthException;
use App\Exceptions\OrderUpdateException;
use App\Exceptions\ProviderNotFoundException;
use App\Http\Requests\ApmMinteRedirectRequest;
use App\Http\Requests\CreateApmOrderRequest;
use App\Http\Requests\CreateApmUpsellsOrderRequest;
use Http\Client\Exception\HttpException;

/**
 * Apm Service class
 */
class ApmService {

    /**
     * Creates Apm payment
     * @param CreateApmOrderRequest $req
     * @return array
     * @throws OrderUpdateException
     * @throws ProviderNotFoundException
     * @throws \App\Exceptions\CustomerUpdateException
     * @throws \App\Exceptions\InvalidParamsException
     * @throws \App\Exceptions\PaymentException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \Exception
     */
    public static function createOrder(CreateApmOrderRequest $req)
    {
        $sku = $req->input('product.sku');
        $qty = $req->input('product.qty');
        $is_warranty = (bool)$req->input('product.is_warranty_checked', false);
        $page_checkout = $req->input('page_checkout', $req->header('Referer'));
        $user_agent = $req->header('User-Agent');
        $ipqs = $req->input('ipqs', null);
        $fingerprint = $req->get('f', null);
        $order_id = $req->get('order');
        $method = $req->get('method');
        $contacts = array_merge(
            $req->get('contact'),
            $req->get('address'),
            ['ip' => $req->ip(), 'email' => strtolower($req->input('contact.email'))]
        );

        $shop_currency = CurrencyService::getCurrency()->code;

        $order = OdinOrder::findExistedOrderForPay($order_id, $req->get('product'));

        $product = PaymentService::getProductByCopIdOrSku($req->get('cop_id'), $sku);

        // get PaymentApi considering domain, product, country and currency
        $domain = Domain::getByName();
        $api = PaymentApiService::getAvailableOne(
            $product->getIdAttribute(),
            $method,
            optional($domain)->getIdAttribute(),
            PaymentService::getProvidersForPay($contacts['country'], $method),
            $shop_currency
        );

        if (empty($api)) {
            logger()->warning('Provider not found', ['country' => $contacts['country'], 'method' => $method]);
            throw new ProviderNotFoundException("Provider not found [{$contacts['country']},{$method}]");
        }

        $params = !empty($page_checkout) ? UtilsService::getParamsFromUrl($page_checkout) : null;
        $affid = AffiliateService::getAttributeByPriority($params['aff_id'] ?? null, $params['affid'] ?? null);

        // refuse fraudulent payment
        PaymentService::fraudCheck($ipqs, $api->payment_provider, $affid, $contacts['email']); // throwable

        PaymentService::addCustomer($contacts); // throwable

        if (empty($order)) {
            $price = PaymentService::getLocalizedPrice($product, (int)$qty, $contacts['country'], $api->payment_provider); // throwable

            $order_product = PaymentService::createOrderProduct($sku, $price, ['is_warranty' => $is_warranty]);

            $order = PaymentService::addOrder([
                'affiliate' => AffiliateService::validateAffiliateID($affid) ? $affid : null,
                'billing_descriptor' => $product->getPaymentBillingDescriptor($contacts['country']),
                'currency' => $price['currency'],
                'exchange_rate' => $price['usd_rate'],
                'fingerprint' => $fingerprint,
                'ip' => $req->ip(),
                'ipqualityscore' => $ipqs,
                'language' => app()->getLocale(),
                'offer' => AffiliateService::getAttributeByPriority($params['offer_id'] ?? null, $params['offerid'] ?? null),
                'products' => [$order_product],
                'page_checkout' => $page_checkout,
                'params' => $params,
                'shop_currency' => $shop_currency,
                'total_price' => $order_product['total_price'],
                'total_price_usd' => $order_product['total_price_usd'],
                'txid' => AffiliateService::getValidTxid($params['txid'] ?? null),
                'type' => OrderService::getOrderTypeByProduct($product),
                'warehouse_id' => $product->warehouse_id
            ]);
            $order->fillShippingData($contacts);
        } else {
            $order_product = $order->getMainProduct(); // throwable
            $order->billing_descriptor  = $product->getPaymentBillingDescriptor($contacts['country']);
            $order->fillShippingData($contacts);
        }

        // create transaction using selected provider
        $payment = [];
        switch ($api->payment_provider):
            case PaymentProviders::MINTE:
                $payment = (new MinteService($api))->payApm(
                    $method,
                    $contacts,
                    [
                        'amount' => $order->total_price,
                        'currency' => $order->currency,
                        'order_id' => $order->getIdAttribute(),
                        'order_desc' => $product->description,
                        'user_agent' => $user_agent,
                        'order_number' => $order->number
                    ]
                );
                break;
            case PaymentProviders::NOVALNET:
                $payment = (new NovalnetService($api))->pay(
                    $method,
                    $contacts,
                    [
                        'amount' => $order->total_price,
                        'currency' => $order->currency,
                        'order_id' => $order->getIdAttribute(),
                        'order_number' => $order->number
                    ]
                );
                break;
        endswitch;

        PaymentService::addTxnToOrder($order, $payment, ['payment_method' => $method]);

        $order_product['txn_hash'] = $payment['hash'];
        $order->addProduct($order_product, true);

        $order->is_flagged = $payment['is_flagged'];
        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }

        return PaymentService::generateCreateOrderResult($order, $payment);
    }

    /**
     * Adds upsells to APM order
     * @param CreateApmUpsellsOrderRequest $req
     * @return array
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public static function createUpsellsOrder(CreateApmUpsellsOrderRequest $req): array
    {
        $upsells = $req->input('upsells', []);
        $user_agent = $req->header('User-Agent');

        $order = OdinOrder::getById($req->get('order')); // throwable
        $order_main_product = $order->getMainProduct(); // throwable
        $order_main_txn = $order->getTxnByHash($order_main_product['txn_hash']); //throwable
        $main_product = OdinProduct::getBySku($order_main_product['sku_code']); // throwable

        $payment = ['status' => Txn::STATUS_FAILED];
        if ((new OrderService())->checkIfUpsellsPossible($order) && PaymentService::isApm($order_main_txn['payment_method'])) {
            $product_srv = new ProductService();
            $products = [];
            $upsell_products = [];
            $checkout_price = 0;
            $is_discount = $req->input('is_discount');
            foreach ($upsells as $key => $item) {
                try {
                    $upsell = $product_srv->getUpsellProductById($main_product, $item['id'], $item['qty'], $order->currency); // throwable
                    $upsell = $product_srv->localizeUpsell($upsell, $order_main_product['sku_code']);
                    if ($is_discount && $order->type === OdinOrder::TYPE_VIRTUAL && $key == 0) {
                        $upsell_price = $upsell->upsellPrices['30p'] ?? $upsell->upsellPrices['30p'][$item['qty']];
                    } else {
                        $upsell_price = $upsell->upsellPrices[$item['qty']];
                    }
                    $upsell_product = PaymentService::createOrderProduct(
                        $upsell->upsell_sku,
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
                    $products[$upsell->upsell_sku] = $upsell;
                    $upsell_products[] = $upsell_product;
                } catch (HttpException $e) {
                    $upsells[$key]['status'] = PaymentService::STATUS_FAIL;
                }
            }

            $api = PaymentApiService::getById($order_main_txn['payment_api_id']);
            if ($checkout_price >= OdinProduct::MIN_PRICE && $api) {
                $checkout_price = CurrencyService::roundValueByCurrencyRules($checkout_price, $order->currency);
                switch ($api->payment_provider):
                    case PaymentProviders::MINTE:
                        $payment = (new MinteService($api))->payApm(
                            $order_main_txn['payment_method'],
                            $order->getShippingData(),
                            [
                                'amount'    => $checkout_price,
                                'currency'  => $order->currency,
                                'order_id'  => $order->getIdAttribute(),
                                'order_number'  => $order->number,
                                'order_desc'    => $main_product->description,
                                'user_agent'    => $user_agent
                            ]
                        );
                        break;
                    case PaymentProviders::NOVALNET:
                        $payment = (new NovalnetService($api))->pay(
                            $order_main_txn['payment_method'],
                            $order->getShippingData(),
                            [
                                'amount'    => $checkout_price,
                                'currency'  => $order->currency,
                                'order_id'  => $order->getIdAttribute(),
                                'order_number'  => $order->number
                            ]
                        );
                        break;
                endswitch;

                // NOTE: re-request order to prevent race condition
                $order = OdinOrder::getById($order->getIdAttribute());

                foreach ($upsell_products as $item) {
                    $item['txn_hash'] = $payment['hash'];
                    $order->addProduct($item);
                }

                PaymentService::addTxnToOrder($order, $payment, $order_main_txn);

                if ($order->status === OdinOrder::STATUS_PAID) {
                    $order->status = OdinOrder::STATUS_HALFPAID;
                }

                $checkout_price += $order_main_product['price'] + $order_main_product['warranty_price'];
                $order->total_price = CurrencyService::roundValueByCurrencyRules($checkout_price, $order->currency);
                $order->total_price_usd = CurrencyService::roundValueByCurrencyRules($order->total_price / $order->exchange_rate, Currency::DEF_CUR);

                if (!$order->save()) {
                    $validator = $order->validate();
                    if ($validator->fails()) {
                        throw new OrderUpdateException(json_encode($validator->errors()->all()));
                    }
                }
            }
        }

        return PaymentService::generateCreateUpsellsOrderResult($order, $upsells, $payment);
    }

    /**
     * Mint-e apm redirect
     * @param ApmMinteRedirectRequest $req
     * @param string $order_id
     * @return array
     * @throws AuthException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public static function minteApm(ApmMinteRedirectRequest $req, string $order_id): array
    {
        $errcode = $req->input('errorcode');
        $errmsg  = $req->input('errormessage');
        $sign    = $req->input('signature');
        $hash    = $req->input('transid');
        $status  = $req->input('status');
        $ts      = $req->input('timestamp', '') ?? '';

        $order = OdinOrder::getById($order_id); // throwable
        $txn = $order->getTxnByHash($hash); // throwable
        $product = $order->getProductByTxnHash($hash, false);

        $handler = new MinteService(PaymentApiService::getById($txn['payment_api_id']));

        if (!$handler->verifySignature($hash, $sign, $ts)) {
            logger()->error('Mint-e apm redirect unauthorized', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $result = $handler->handleApm($txn, ['errcode' => $errcode, 'errmsg' => $errmsg, 'status' => $status]);
        $result['is_main'] = $product['is_main'] ?? null;

        return $result;
    }

}
