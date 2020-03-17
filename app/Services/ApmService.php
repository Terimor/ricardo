<?php

namespace App\Services;

use App\Http\Requests\ApmRedirectRequest;
use App\Http\Requests\CreateApmOrderRequest;
use App\Http\Requests\CreateApmUpsellsOrderRequest;
use App\Models\Txn;
use App\Models\Domain;
use App\Models\Currency;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Exceptions\AuthException;
use App\Exceptions\OrderUpdateException;
use App\Exceptions\ProviderNotFoundException;
use Http\Client\Exception\HttpException;
use App\Constants\PaymentProviders;

/**
 * Apm Service class
 */
class ApmService {

    /**
     * Creates Apm payment
     * @param  CreateApmOrderRequest $req
     * @return array
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

        logger()->info('Apm req', ['data' => $req->getContent()]);

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

        logger()->info('Apm api', ['api' => $api->toJson()]);

        if (empty($api)) {
            logger()->warning('Provider not found', ['country' => $contacts['country'], 'method' => $method]);
            throw new ProviderNotFoundException("Provider not found [{$contacts['country']},{$method}]");
        }

        $params = !empty($page_checkout) ? UtilsService::getParamsFromUrl($page_checkout) : null;
        $affid = AffiliateService::getAttributeByPriority($params['aff_id'] ?? null, $params['affid'] ?? null);

        // refuse fraudulent payment
        PaymentService::fraudCheck($ipqs, $api->payment_provider, $affid); // throwable

        PaymentService::addCustomer($contacts); // throwable

        if (empty($order)) {
            $price = PaymentService::getLocalizedPrice($product, (int)$qty, $contacts['country'], $api->payment_provider); // throwable

            $order_product = PaymentService::createOrderProduct($sku, $price, ['is_warranty' => $is_warranty]);

            $order = PaymentService::addOrder([
                'billing_descriptor'    => $product->getPaymentBillingDescriptor($contacts['country']),
                'currency'              => $price['currency'],
                'exchange_rate'         => $price['usd_rate'],
                'fingerprint'           => $fingerprint,
                'total_paid'            => 0,
                'total_paid_usd'        => 0,
                'total_refunded_usd'    => 0,
                'total_price'           => $order_product['total_price'],
                'total_price_usd'       => $order_product['total_price_usd'],
                'txns_fee_usd'          => 0,
                'installments'          => 0,
                'is_reduced'            => false,
                'is_invoice_sent'       => false,
                'is_survey_sent'        => false,
                'is_flagged'            => false,
                'is_refunding'          => false,
                'is_refunded'           => false,
                'is_qc_passed'          => false,
                'customer_email'        => $contacts['email'],
                'customer_first_name'   => $contacts['first_name'],
                'customer_last_name'    => $contacts['last_name'],
                'customer_phone'        => $contacts['phone']['country_code'] . UtilsService::preparePhone($contacts['phone']['number']),
                'customer_doc_id'       => $contacts['document_number'] ?? null,
                'ip'                    => $contacts['ip'],
                'shipping_country'      => $contacts['country'],
                'shipping_zip'          => $contacts['zip'],
                'shipping_state'        => $contacts['state'] ?? null,
                'shipping_city'         => $contacts['city'],
                'shipping_street'       => $contacts['street'],
                'shipping_street2'      => $contacts['district'] ?? null,
                'shipping_building'     => $contacts['building'] ?? null,
                'shipping_apt'          => $contacts['complement'] ?? null,
                'language'              => app()->getLocale(),
                'txns'                  => [],
                'shop_currency'         => $shop_currency,
                'warehouse_id'          => $product->warehouse_id,
                'products'              => [$order_product],
                'page_checkout'         => $page_checkout,
                'params'                => $params,
                'offer'                 => AffiliateService::getAttributeByPriority($params['offer_id'] ?? null, $params['offerid'] ?? null),
                'affiliate'             => AffiliateService::validateAffiliateID($affid) ? $affid : null,
                'txid'                  => AffiliateService::getValidTxid($params['txid'] ?? null),
                'ipqualityscore'        => $ipqs,
                'ip'                    => $req->ip()
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
                $payment = (new MinteService($api))->payApm($method, $contacts, [
                    'amount'    => $order->total_price,
                    'currency'  => $order->currency,
                    'domain'    => optional($domain)->name,
                    'order_id'  => $order->getIdAttribute(),
                    'order_number'  => $order->number,
                    'order_desc'    => $product->description,
                    'user_agent'    => $user_agent,
                ]);
                break;
        endswitch;

        PaymentService::addTxnToOrder($order, $payment, ['payment_method' => $method]);

        $order_product['txn_hash'] = $payment['hash'];
        $order->addProduct($order_product, true);

        // cache token (encrypted card)
        CardService::setCardToken($order->number, $payment['token'] ?? null);

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
     * @param  CreateApmUpsellsOrderRequest $req
     * @return array
     */
    public static function createUpsellsOrder(CreateApmUpsellsOrderRequest $req): array
    {
        $upsells = $req->input('upsells', []);
        $user_agent = $req->header('User-Agent');

        $order = OdinOrder::getById($req->get('order')); // throwable
        $order_main_product = $order->getMainProduct(); // throwable
        $order_main_txn = $order->getTxnByHash($order_main_product['txn_hash']); //throwable
        $main_product = OdinProduct::getBySku($order_main_product['sku_code']); // throwable

        // prepare upsells result
        $upsells = array_map(function($v) {
            $v['status'] = PaymentService::STATUS_FAIL;
            return $v;
        }, $upsells);

        $is_upsells_possible = (new OrderService())->checkIfUpsellsPossible($order);

        $payment = [];
        if ($is_upsells_possible && PaymentService::isApm($order_main_txn['payment_method'])) {
            $products = [];
            $upsell_products = [];
            $checkout_price = 0;
            foreach ($upsells as $key => $item) {
                try {
                    $product = (new ProductService())->getUpsellProductById($main_product, $item['id'], $item['qty'], $order->currency); // throwable
                    $upsell_price = $product->upsellPrices[$item['qty']];
                    $upsell_product = PaymentService::createOrderProduct(
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
                    $upsells[$key]['status'] = PaymentService::STATUS_FAIL;
                }
            }

            $api = PaymentApiService::getById($order_main_txn['payment_api_id']);
            if ($checkout_price >= OdinProduct::MIN_PRICE && $api) {
                $checkout_price = CurrencyService::roundValueByCurrencyRules($checkout_price, $order->currency);
                switch ($api->payment_provider):
                    case PaymentProviders::MINTE:
                        $domain = Domain::getByName();
                        $payment = (new MinteService($api))->payApm(
                            $order_main_txn['payment_method'],
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
                                'domain'    => optional($domain)->name,
                                'order_id'  => $order->getIdAttribute(),
                                'order_number'  => $order->number,
                                'order_desc'    => $main_product->description,
                                'user_agent'    => $user_agent
                            ]
                        );
                        break;
                endswitch;

                // update order
                $upsells = array_map(function($v) use ($payment) {
                    if ($payment['status'] === Txn::STATUS_AUTHORIZED) {
                        $v['status'] = PaymentService::STATUS_OK;
                    } else {
                        $v['status'] = PaymentService::STATUS_FAIL;
                        $v['errors'] = $payment['errors'];
                    }
                    return $v;
                }, $upsells);

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

        return [
            'id'             => $order_main_product['txn_hash'],
            'order_currency' => $order->currency,
            'order_number'   => $order->number,
            'order_id'       => $order->getIdAttribute(),
            'status'         => $order_main_txn['status'] !== Txn::STATUS_FAILED ? PaymentService::STATUS_OK : PaymentService::STATUS_FAIL,
            'redirect_url'   => $payment['redirect_url'] ?? null,
            'upsells'        => $upsells
        ];
    }

    /**
     * Mint-e apm redirect
     * @param  ApmRedirectRequest $req
     * @param  string $order_id
     * @return array
     */
    public static function minteApm(ApmRedirectRequest $req, string $order_id): array
    {
        $errcode = $req->input('errorcode');
        $errmsg  = $req->input('errormessage');
        $sign    = $req->input('signature');
        $hash    = $req->input('transid');
        $status  = $req->input('status');
        $ts      = $req->input('timestamp', '') ?? '';

        $order = OdinOrder::getById($order_id); // throwable
        $product = $order->getProductByTxnHash($hash); // throwable
        $txn = $order->getTxnByHash($hash); // throwable

        $handler = new MinteService(PaymentApiService::getById($txn['payment_api_id']));

        if (!$handler->verifySignature($hash, $sign, $ts)) {
            logger()->error('Mint-e apm redirect unauthorized', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $result = $handler->handleApm($txn, ['errcode' => $errcode, 'errmsg' => $errmsg, 'status' => $status]);
        $result['is_main'] = $product['is_main'];

        return $result;
    }

}
