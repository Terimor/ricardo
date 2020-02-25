<?php

namespace App\Services;

use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use App\Http\Requests\PaymentCardMinte3dsRequest;
use App\Models\Txn;
use App\Models\Domain;
use App\Models\Currency;
use App\Models\OdinOrder;
use App\Models\OdinProduct;
use App\Exceptions\AuthException;
use App\Exceptions\OrderUpdateException;
use App\Exceptions\ProviderNotFoundException;
use App\Constants\PaymentProviders;
use App\Mappers\PaymentMethodMapper;
use Illuminate\Support\Facades\Cache;
use Http\Client\Exception\HttpException;

/**
 * Card Service class
 */
class CardService {

    const CARD_CREDIT = 'credit';
    const CARD_DEBIT  = 'debit';

    const CACHE_TOKEN_PREFIX    = 'CardToken';
    const CACHE_ERRORS_PREFIX   = 'PayErrors';
    const CACHE_TOKEN_TTL_MIN   = 15;

    /**
     * Creates a new order
     * @param PaymentCardCreateOrderRequest $req
     * @return array
     */
    public static function createOrder(PaymentCardCreateOrderRequest $req): array
    {
        ['sku' => $sku, 'qty' => $qty] = $req->get('product');
        $is_warranty = (bool)$req->input('product.is_warranty_checked', false);
        $page_checkout = $req->input('page_checkout', $req->header('Referer'));
        $user_agent = $req->header('User-Agent');
        $kount_session_id = $req->input('kount_session_id', null);
        $ipqs = $req->input('ipqs', null);
        $card = $req->get('card');
        $fingerprint = $req->get('f', null);
        $order_id = $req->get('order');
        $installments = (int)$req->input('card.installments', 0);
        $shop_currency = CurrencyService::getCurrency()->code;
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

        $domain = Domain::getByName();
        $api = PaymentApiService::getAvailableOne(
            $product->getIdAttribute(),
            $method,
            optional($domain)->getIdAttribute(),
            PaymentService::getProvidersForPay($contact['country'], $method),
            $shop_currency
        );
        if (empty($api)) {
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

        $params = !empty($page_checkout) ? UtilsService::getParamsFromUrl($page_checkout) : null;
        $affid = AffiliateService::getAttributeByPriority($params['aff_id'] ?? null, $params['affid'] ?? null);

        PaymentService::fraudCheck($ipqs, $api->payment_provider, $affid); // throwable

        PaymentService::addCustomer($contact); // throwable

        if (empty($order)) {
            $price = PaymentService::getLocalizedPrice($product, (int)$qty, $contact['country'], $api->payment_provider); // throwable

            $order_product = PaymentService::createOrderProduct($sku, $price, ['is_warranty' => $is_warranty]);

            $order = PaymentService::addOrder([
                'billing_descriptor'    => $product->getPaymentBillingDescriptor($contact['country']),
                'currency'              => $price['currency'],
                'exchange_rate'         => $price['usd_rate'],
                'fingerprint'           => $fingerprint,
                'total_paid'            => 0,
                'total_paid_usd'        => 0,
                'total_refunded_usd'    => 0,
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
                'shipping_state'        => $contact['state'] ?? null,
                'shipping_city'         => $contact['city'],
                'shipping_street'       => $contact['street'],
                'shipping_street2'      => $contact['district'] ?? null,
                'shipping_building'     => $contact['building'] ?? null,
                'shipping_apt'          => $contact['complement'] ?? null,
                'shop_currency'         => CurrencyService::getCurrency()->code,
                'warehouse_id'          => $product->warehouse_id,
                'products'              => [$order_product],
                'page_checkout'         => $page_checkout,
                'params'                => $params,
                'offer'                 => AffiliateService::getAttributeByPriority($params['offer_id'] ?? null, $params['offerid'] ?? null),
                'affiliate'             => AffiliateService::validateAffiliateID($affid) ? $affid : null,
                'txid'                  => AffiliateService::getValidTxid($params['txid'] ?? null),
                'ipqualityscore'        => $ipqs
            ]);
        } else {
            $order_product = $order->getMainProduct(); // throwable

            $order->billing_descriptor  = $product->getPaymentBillingDescriptor($contact['country']);
            $order->customer_email      = $contact['email'];
            $order->customer_first_name = $contact['first_name'];
            $order->customer_last_name  = $contact['last_name'];
            $order->customer_phone      = $contact['phone']['country_code'] . UtilsService::preparePhone($contact['phone']['number']);
            $order->customer_doc_id     = $contact['document_number'] ?? null;
            $order->shipping_country    = $contact['country'];
            $order->shipping_zip        = $contact['zip'];
            $order->shipping_state      = $contact['state'] ?? null;
            $order->shipping_city       = $contact['city'];
            $order->shipping_street     = $contact['street'];
            $order->shipping_street2    = $contact['district'] ?? null;
            $order->shipping_building   = $contact['building'] ?? null;
            $order->shipping_apt        = $contact['complement'] ?? null;
            $order->installments        = $installments;
        }
        // create payment
        $payment = [];
        switch ($api->payment_provider):
            case PaymentProviders::APPMAX:
                $appmax = new AppmaxService($api);
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
                break;
            case PaymentProviders::EBANX:
                $ebanx = new EbanxService($api);
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
                break;
            case PaymentProviders::CHECKOUTCOM:
                $checkout = new CheckoutDotComService($api);
                $payment = $checkout->payByCard($card, $contact, [
                    'id' => $order->getIdAttribute(),
                    '3ds' => PaymentService::checkIs3dsNeeded(
                        $method,
                        $contact['country'],
                        PaymentProviders::CHECKOUTCOM,
                        $order->affiliate,
                        (array)$ipqs
                    ),
                    'amount' => $order->total_price,
                    'number' => $order->number,
                    'currency' => $order->currency,
                    'description' => $product->product_name,
                    // TODO: remove city hardcode
                    'billing_descriptor' => ['name' => $order->billing_descriptor, 'city' => 'Msida']
                ]);
                if ($payment['status'] !== Txn::STATUS_FAILED) {
                    $payment['token'] = $checkout->requestToken($card, $contact);
                }
                break;
            case PaymentProviders::BLUESNAP:
                $bluesnap = new BluesnapService($api);
                $payment = $bluesnap->payByCard(
                    $card,
                    $contact,
                    [
                        'amount' => $order->total_price,
                        'currency' => $order->currency,
                        'order_id' => $order->getIdAttribute(),
                        'billing_descriptor' => $order->billing_descriptor,
                        'kount_session_id' => $kount_session_id,
                        '3ds' => PaymentService::checkIs3dsNeeded(
                            $method,
                            $contact['country'],
                            PaymentProviders::BLUESNAP,
                            $order->affiliate,
                            (array)$ipqs
                        )
                    ]
                );
                break;
            case PaymentProviders::MINTE:
                $minte = new MinteService($api);
                $payment = $minte->payByCard($card, $contact, [
                    '3ds' => PaymentService::checkIs3dsNeeded(
                        $method,
                        $contact['country'],
                        PaymentProviders::MINTE,
                        $order->affiliate,
                        (array)$ipqs
                    ),
                    'amount'    => $order->total_price,
                    'currency'  => $order->currency,
                    'domain'    => optional($domain)->name,
                    'order_id'  => $order->getIdAttribute(),
                    'order_number'  => $order->number,
                    'product_id'    => $product->getIdAttribute(),
                    'user_agent'    => $user_agent,
                    'descriptor'    => $order->billing_descriptor
                ]);
                if ($payment['status'] === Txn::STATUS_CAPTURED) {
                    $capture = $minte->capture($payment['hash'], ['amount' => $order->total_price, 'currency' => $order->currency]);
                    PaymentService::logTxn(array_merge($capture, ['payment_method' => $method]));
                    $payment['status'] = $capture['status'];
                    $payment['capture_hash'] = $capture['hash'];
                }
                break;
        endswitch;

        PaymentService::addTxnToOrder($order, $payment, [
            'payment_method' => $method,
            'card_number' => UtilsService::prepareCardNumber($card['number']),
            'card_type' => $card['type'] ?? null
        ]);

        $order_product['txn_hash'] = $payment['hash'];
        $order->addProduct($order_product, true);

        // check is this fallback
        if (!empty($payment['fallback'])) {

            $reply = PaymentService::fallbackOrder($order, $card, [
                'method'     => $method,
                'provider'   => $api->payment_provider,
                'useragent'  => $user_agent
            ]);

            if ($reply['status']) {
                $payment = $reply['payment'];
                $order_product = $order->getMainProduct(); // throwable
                $order_product['txn_hash'] = $payment['hash'];
                $order->addProduct($order_product, true);
                PaymentService::addTxnToOrder($order, $payment, [
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
            $order = PaymentService::approveOrder($payment, $payment['payment_provider']);
        }

        $result = [
            'order_currency' => $order->currency,
            'order_number'   => $order->number,
            'order_amount'   => $payment['value'],
            'order_id'       => $order->getIdAttribute(),
            'status'         => PaymentService::STATUS_FAIL,
            'id'             => null
        ];

        if ($payment['status'] !== Txn::STATUS_FAILED) {
            $result['id'] = $payment['hash'];
            $result['status'] = PaymentService::STATUS_OK;
            $result['redirect_url'] = !empty($payment['redirect_url']) ? stripslashes($payment['redirect_url']) : null;
            $result['bs_pf_token'] = $payment['bs_pf_token'] ?? null;
        } else {
            $result['errors'] = $payment['errors'];
            PaymentService::cacheOrderErrors(['number' => $order->number, 'errors' => $payment['errors']]);
        }

        return array_filter($result);
    }

    /**
     * Adds upsells to order using CardToken
     * @param  PaymentCardCreateUpsellsOrderRequest $req
     * @return array
     */
    public static function createUpsellsOrder(PaymentCardCreateUpsellsOrderRequest $req): array
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

        $card_token = null;
        $is_upsells_possible = (new OrderService())->checkIfUpsellsPossible($order);
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
                if ($api->payment_provider === PaymentProviders::APPMAX) {
                    $appmax = new AppmaxService($api);
                    $payment = $appmax->payByToken(
                        $card_token,
                        [
                            'street'            => $order->shipping_street,
                            'city'              => $order->shipping_city,
                            'country'           => $order->shipping_country,
                            'state'             => $order->shipping_state,
                            'district'          => $order->shipping_street2,
                            'building'          => $order->shipping_building,
                            'complement'        => $order->shipping_apt,
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
                } elseif ($api->payment_provider === PaymentProviders::EBANX) {
                    $ebanx = new EbanxService($api);
                    $payment = $ebanx->payByToken(
                        $card_token,
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
                } elseif ($api->payment_provider === PaymentProviders::CHECKOUTCOM) {
                    $checkout = new CheckoutDotComService($api);
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
                } elseif ($api->payment_provider === PaymentProviders::MINTE) {
                    $domain = Domain::getByName();
                    $minte = new MinteService($api);
                    $payment = $minte->payByToken(
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
                            'domain'    => optional($domain)->name,
                            'order_id'  => $order->getIdAttribute(),
                            'descriptor'    => $order->billing_descriptor,
                            'order_number'  => $order->number,
                            'user_agent'    => $user_agent,
                            'payment_api_id' => $order_main_txn['payment_api_id']
                        ]
                    );
                    if ($payment['status'] === Txn::STATUS_CAPTURED) {
                        $capture = $minte->capture($payment['hash'], ['amount' => $checkout_price, 'currency' => $order->currency]);
                        PaymentService::logTxn(array_merge($capture, ['payment_method' => $order_main_txn['payment_method']]));
                        $payment['status'] = $capture['status'];
                        $payment['capture_hash'] = $capture['hash'];
                    }
                } elseif ($api->payment_provider === PaymentProviders::BLUESNAP) {
                    $bluesnap = new BluesnapService($api);
                    $payment = $bluesnap->payByVaultedShopperId(
                        $order_main_txn['payer_id'],
                        [
                            'amount'    => $checkout_price,
                            'currency'  => $order->currency,
                            'billing_descriptor' => $order->billing_descriptor
                        ]
                    );
                }

                // update order
                $upsells = array_map(function($v) use ($payment) {
                    if (in_array($payment['status'], [Txn::STATUS_CAPTURED, Txn::STATUS_APPROVED])) {
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

                // reset flag if txn is approved
                if ($payment['status'] === Txn::STATUS_APPROVED) {
                    $order->is_invoice_sent = false;
                }

                if (!$order->save()) {
                    $validator = $order->validate();
                    if ($validator->fails()) {
                        throw new OrderUpdateException(json_encode($validator->errors()->all()));
                    }
                }

                // approve order if txn is approved
                if ($payment['status'] === Txn::STATUS_APPROVED) {
                    $order = PaymentService::approveOrder($payment, $payment['payment_provider']);
                }
            }
        }

        return [
            'order_currency'    => $order->currency,
            'order_number'      => $order->number,
            'order_id'          => $order->getIdAttribute(),
            'id'                => $order_main_product['txn_hash'],
            'status'            => $order_main_txn['status'] !== Txn::STATUS_FAILED ? PaymentService::STATUS_OK : PaymentService::STATUS_FAIL,
            'upsells'           => $upsells
        ];
    }

    /**
     * Resolves Blusnap 3ds payment
     * @param  string $order_id
     * @param  string $ref
     * @return array
     */
    public static function completeBs3dsOrder(string $order_id, string $ref): array
    {
        $user_agent = request()->header('User-Agent');

        $order = OdinOrder::getById($order_id); // throwable
        $order_product = $order->getMainProduct(); // throwable
        $order_txn = $order->getTxnByHash($order_product['txn_hash']); // throwable
        $order->dropTxn($order_txn['hash']);

        $result = [
            'order_currency' => $order->currency,
            'order_number'   => $order->number,
            'order_amount'   => $order_txn['value'],
            'order_id'       => $order->getIdAttribute(),
            'status'         => PaymentService::STATUS_FAIL
        ];

        if (empty($order_txn['payer_id'])) {
            return $result;
        }

        $api = PaymentApiService::getById($order_txn['payment_api_id']);
        $bluesnap = new BluesnapService($api);
        $payment = $bluesnap->payByVaultedShopperId(
            $order_txn['payer_id'],
            [
                '3ds_ref'   => $ref,
                'amount'    => $order_txn['value'],
                'currency'  => $order->currency,
                'billing_descriptor' => $order->billing_descriptor
            ]
        );

        PaymentService::addTxnToOrder($order, $payment, $order_txn);

        $order_product['txn_hash'] = $payment['hash'];
        $order->addProduct($order_product, true);

        // check is this fallback
        $cardtk = self::getCardToken($order->number, false);
        if (!empty($payment['fallback']) && $cardtk) {
            $reply = PaymentService::fallbackOrder(
                $order,
                json_decode(BluesnapService::decrypt($cardtk, $order_id), true),
                [
                    'method'     => $order_txn['payment_method'],
                    'provider'   => PaymentProviders::BLUESNAP,
                    'useragent'  => $user_agent
                ]
            );

            if ($reply['status']) {
                $payment = $reply['payment'];
                $order_product = $order->getMainProduct(); // throwable
                $order_product['txn_hash'] = $payment['hash'];
                $order->addProduct($order_product, true);
                PaymentService::addTxnToOrder($order, $payment, $order_txn);
            }
        }

        if (!$order->save()) {
            $validator = $order->validate();
            if ($validator->fails()) {
                throw new OrderUpdateException(json_encode($validator->errors()->all()));
            }
        }

        if ($payment['status'] !== Txn::STATUS_FAILED) {
            $result['id'] = $payment['hash'];
            $result['status'] = PaymentService::STATUS_OK;
            $result['redirect_url'] = !empty($payment['redirect_url']) ? stripslashes($payment['redirect_url']) : null;
        } else {
            $result['errors'] = $payment['errors'];
            PaymentService::cacheOrderErrors(['number' => $order->number, 'errors' => $payment['errors']]);
        }

        return array_filter($result);
    }

    /**
     * Mint-e 3ds redirect
     * @param  PaymentCardMinte3dsRequest $req
     * @param string $order_id
     * @return array
     */
    public static function minte3ds(PaymentCardMinte3dsRequest $req, string $order_id): array
    {
        $errcode    = $req->input('errorcode');
        $errmsg     = $req->input('errormessage');
        $sign       = $req->input('signature');
        $txn_hash   = $req->input('transid');
        $txn_status = $req->input('status');
        $txn_ts     = $req->input('timestamp', '') ?? '';

        $order = OdinOrder::getById($order_id); // throwable
        $order_txn = $order->getTxnByHash($txn_hash); // throwable

        $minte = new MinteService(PaymentApiService::getById($order_txn['payment_api_id']));

        if (!$minte->verifySignature($txn_hash, $sign, $txn_ts)) {
            logger()->warning('Mint-e unauthorized redirect', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $payment = $minte->handle3ds($order_txn, ['errcode' => $errcode, 'errmsg' => $errmsg, 'status' => $txn_status]);
        if ($payment['status'] === Txn::STATUS_CAPTURED) {
            $capture = $minte->capture($payment['hash'], ['amount' => $order_txn['value'], 'currency' => $order->currency]);
            PaymentService::logTxn(array_merge($capture, ['payment_method' => $order_txn['payment_method']]));
            $payment['status'] = $capture['status'];
            $payment['capture_hash'] = $capture['hash'];
        }

        $result = ['status' => PaymentService::STATUS_FAIL];
        if ($payment['status'] === Txn::STATUS_APPROVED) {
            $result = ['status' => PaymentService::STATUS_OK];
            PaymentService::approveOrder($payment, PaymentProviders::MINTE);
        } else {
            $order = PaymentService::rejectTxn($payment, PaymentProviders::MINTE);

            PaymentService::cacheOrderErrors(array_merge($payment, ['number' => $order->number]));

            $cardtk = self::getCardToken($order->number, false);

            logger()->info("Pre-Fallback [{$order->number}]", ['cardtk' => !!$cardtk, 'fallback' => !empty($payment['fallback'])]);

            if (!empty($payment['fallback']) && $cardtk) {
                $reply = PaymentService::fallbackOrder(
                    $order,
                    json_decode(MinteService::decrypt($cardtk, $order_id), true),
                    ['provider' => PaymentProviders::MINTE, 'method' => $order_txn['payment_method']]
                );

                if ($reply['status']) {
                    $result = [
                        'amount'        => $reply['payment']['value'],
                        'currency'      => $order->currency,
                        'status'        => PaymentService::STATUS_OK,
                        'redirect_url'  => $reply['payment']['redirect_url'] ?? null,
                        'bs_pf_token'   => $reply['payment']['bs_pf_token'] ?? null
                    ];

                    $order_product = $order->getMainProduct(); // throwable
                    $order_product['txn_hash'] = $reply['payment']['hash'];
                    $order->addProduct($order_product, true);
                    PaymentService::addTxnToOrder($order, $reply['payment'], $order_txn);
                    $order->is_flagged = $reply['payment']['is_flagged'];

                    if (!$order->save()) {
                        $validator = $order->validate();
                        if ($validator->fails()) {
                            throw new OrderUpdateException(json_encode($validator->errors()->all()));
                        }
                    }

                    if ($reply['payment']['status'] === Txn::STATUS_FAILED) {
                        PaymentService::cacheOrderErrors(array_merge($reply['payment'], ['number' => $order->number]));
                        $result = ['status' => PaymentService::STATUS_FAIL];
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
    public static function ebanxNotification(array $hashes): void
    {
        foreach ($hashes as $hash) {
            $order = OdinOrder::getByTxnHash($hash, PaymentProviders::EBANX, false);

            if (!$order) {
                logger()->warning('Ebanx: order not found', ['hash' => $hash]);
                continue;
            }

            $txn = $order->getTxnByHash($hash, false);

            if (empty($txn['payment_api_id'])) {
                logger()->warning('Ebanx: payment_api_id not found', ['order' => $order->number, 'hash' => $hash]);
                continue;
            }

            $ebanx = new EbanxService(PaymentApiService::getById($txn['payment_api_id']));

            $payment = $ebanx->requestStatusByHash($hash, $txn ?? []);

            if (!empty($payment['number'])) {
                // prevention of race condition
                usleep(mt_rand(0, 2000) * 1000);

                PaymentService::approveOrder($payment, PaymentProviders::EBANX);
            } else {
                logger()->warning('Ebanx: payment not found', ['hash' => $hash]);
            }
        }
    }

    /**
     * Handles Appmax webhook
     * @param string $event
     * @param array  $data
     * @return void
     */
    public static function appmaxWebhook(string $event, array $data): void
    {
        $order = OdinOrder::getByTxnHash($data['id'], PaymentProviders::APPMAX); //throwable
        $txn = $order->getTxnByHash((string)$data['id'], false);

        $api = PaymentApiService::getById($txn['payment_api_id']);
        $appmax = new AppmaxService($api);
        $reply = $appmax->validateWebhook($event, $data);

        if ($reply['status']) {
            PaymentService::approveOrder($reply['txn'], PaymentProviders::APPMAX);
        }
    }

    /**
     * Captures payment
     * @param  string $order_id
     * @param  string $txn_hash
     * @return bool
     */
    public static function capture(string $order_id, string $txn_hash): bool
    {
        $order = OdinOrder::getById($order_id); //throwable
        $txn = $order->getTxnByHash($txn_hash); //throwable

        $result = false;
        if ($txn['status'] === Txn::STATUS_AUTHORIZED) {
            if ($txn['payment_provider'] === PaymentProviders::CHECKOUTCOM) {
                $api = PaymentApiService::getById($txn['payment_api_id']);
                $checkout = new CheckoutDotComService($api);
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
    public static function void(string $order_id, string $txn_hash): bool
    {
        $order = OdinOrder::getById($order_id); //throwable
        $txn = $order->getTxnByHash($txn_hash); //throwable

        $result = false;
        if ($txn['status'] === Txn::STATUS_AUTHORIZED) {
            if ($txn['payment_provider'] === PaymentProviders::CHECKOUTCOM) {
                $api = PaymentApiService::getById($txn['payment_api_id']);
                $checkout = new CheckoutDotComService($api);
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
     * Tries to refund payment
     * @param  string $order_id
     * @param  string $txn_hash
     * @param  string $reason
     * @param  float|null  $amount
     * @return array
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
                    if (empty($txn['capture_hash'])) {
                        $result['errors'] = ["Transaction [$txn_hash] cannot be refunded"];
                        break;
                    }
                    $result = (new MinteService($api))->refund($txn['capture_hash'], $amount ?? $txn['value']);
                    break;
                default:
                    $result['errors'] = ["Refund for {$txn['payment_provider']} not implemented yet. [$txn_hash]"];
                    logger()->info("PaymentService: refund for {$txn['payment_provider']} not implemented yet");
            endswitch;
        }
        return $result;
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
        $order = OdinOrder::getByNumber($number); // throwable
        $txn = $order->getTxnByHash($hash); // throwable
        $api = PaymentApiService::getById($txn['payment_api_id']);
        return new CheckoutDotComService($api);
    }

    /**
     * Returns BluesnapService by order number
     * @param  string $number
     * @param  string $hash
     * @return BluesnapService
     */
    public static function getBluesnapService(string $number, string $hash): BluesnapService
    {
        $order = OdinOrder::getByNumber($number); //throwable
        $txn = $order->getTxnByHash($hash, false);
        $api = PaymentApiService::getById($txn['payment_api_id']);
        return new BluesnapService($api);
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
}
