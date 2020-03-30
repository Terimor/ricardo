<?php

namespace App\Services;

use App\Exceptions\PaymentException;
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
     * @param OdinOrder $order
     * @param string $provider
     * @param string|null $cc_tkn
     * @param string|null $payer_id
     * @return bool
     */
    private static function isUpsellsAvailable(OdinOrder $order, string $provider, ?string $cc_tkn, ?string $payer_id): bool
    {
        $is_upsells_possible = (new OrderService())->checkIfUpsellsPossible($order);
        if ($is_upsells_possible) {
            if (!in_array($provider, [PaymentProviders::BLUESNAP, PaymentProviders::STRIPE])) {
                $is_upsells_possible = !!$cc_tkn;
            } else {
                $is_upsells_possible = !!$payer_id;
            }
        }
        return $is_upsells_possible;
    }

    /**
     * Creates a new order
     * @param PaymentCardCreateOrderRequest $req
     * @return array
     * @throws OrderUpdateException
     * @throws ProviderNotFoundException
     * @throws \App\Exceptions\CustomerUpdateException
     * @throws \App\Exceptions\InvalidParamsException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\PaymentException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     * @throws \Exception
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

        $order = OdinOrder::findExistedOrderForPay($order_id, $req->get('product'));

        $product = PaymentService::getProductByCopIdOrSku($req->get('cop_id'), $sku);

        // get PaymentApi considering domain, product, country and currency
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

        // refuse fraudulent payment
        PaymentService::fraudCheck($ipqs, $api->payment_provider, $affid, $contact['email']); // throwable

        $customer = PaymentService::addCustomer($contact); // throwable

        // if order doesn't exist add it
        if (empty($order)) {
            $price = PaymentService::getLocalizedPrice($product, (int)$qty, $contact['country'], $api->payment_provider); // throwable

            $order_product = PaymentService::createOrderProduct($sku, $price, ['is_warranty' => $is_warranty]);

            $order = PaymentService::addOrder([
                'billing_descriptor' => $product->getPaymentBillingDescriptor($contact['country']),
                'total_price_usd'   => $order_product['total_price_usd'],
                'ipqualityscore'    => $ipqs,
                'currency'      => $price['currency'],
                'exchange_rate' => $price['usd_rate'],
                'fingerprint'   => $fingerprint,
                'total_price'   => $order_product['total_price'],
                'installments'  => $installments,
                'language'      => app()->getLocale(),
                'shop_currency' => CurrencyService::getCurrency()->code,
                'warehouse_id'  => $product->warehouse_id,
                'products'      => [$order_product],
                'page_checkout' => $page_checkout,
                'params'        => $params,
                'offer'         => AffiliateService::getAttributeByPriority($params['offer_id'] ?? null, $params['offerid'] ?? null),
                'affiliate'     => AffiliateService::validateAffiliateID($affid) ? $affid : null,
                'txid'          => AffiliateService::getValidTxid($params['txid'] ?? null),
                'ip'            => $req->ip()
            ]);
            $order->fillShippingData($contact);
        } else {
            $order_product = $order->getMainProduct(); // throwable
            $order->billing_descriptor  = $product->getPaymentBillingDescriptor($contact['country']);
            $order->installments        = $installments;
            $order->fillShippingData($contact);
        }
        // create transaction using selected provider
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
                    'order_id'  => $order->getIdAttribute(),
                    'order_number'  => $order->number,
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
            case PaymentProviders::STRIPE:
                $handle = new StripeService($api);
                $payment = $handle->payByCard($card, $contact, [
                    '3ds' => PaymentService::checkIs3dsNeeded(
                        $method,
                        $contact['country'],
                        PaymentProviders::MINTE,
                        $order->affiliate,
                        (array)$ipqs
                    ),
                    'amount' => $order->total_price,
                    'currency' => $order->currency,
                    'order_id' => $order->getIdAttribute(),
                    'order_number' => $order->number,
                    'installments' => $installments,
                    'billing_descriptor' => $order->billing_descriptor
                ]);
                break;
        endswitch;

        PaymentService::addTxnToOrder($order, $payment, [
            'payment_method' => $method,
            'card_number' => UtilsService::prepareCardNumber($card['number']),
            'card_type' => $card['type'] ?? null
        ]);

        $order_product['txn_hash'] = $payment['hash'];
        $order->addProduct($order_product, true);

        if (!empty($payment['fallback'])) {

            // try to pay with fallback provider
            $reply = PaymentService::fallbackOrder($order, $card, [
                'method'     => $method,
                'provider'   => $api->payment_provider,
                'useragent'  => $user_agent
            ]);

            // when fallback is well done update transaction data
            if ($reply['status']) {
                $payment = $reply['payment'];
                $order_product = $order->getMainProduct(); // throwable
                $order_product['txn_hash'] = $payment['hash'];
                $order->addProduct($order_product, true);
                PaymentService::addTxnToOrder($order, $payment, [
                    'payment_method' => $method,
                    'card_number' => UtilsService::prepareCardNumber($card['number']),
                    'card_type' => $card['type'] ?? null,
                    'is_fallback' => true
                ]);
            }
        }

        // cache token (encrypted card)
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
        // switch customer to Buyer if transaction isn't failed
        if ($payment['status'] !== Txn::STATUS_FAILED) {
            $customer->switchToBuyer();
        }

        return PaymentService::generateCreateOrderResult($order, $payment);
    }

    /**
     * Adds upsells to order using CardToken
     * @param PaymentCardCreateUpsellsOrderRequest $req
     * @return array
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public static function createUpsellsOrder(PaymentCardCreateUpsellsOrderRequest $req): array
    {
        $upsells = $req->input('upsells', []);
        $user_agent = $req->header('User-Agent');

        $order = OdinOrder::getById($req->get('order')); // throwable
        $order_main_product = $order->getMainProduct(); // throwable
        $order_main_txn = $order->getTxnByHash($order_main_product['txn_hash']); //throwable
        $main_product = OdinProduct::getBySku($order_main_product['sku_code']); // throwable

        $card_token = self::getCardToken($order->number);

        $payment = ['status' => Txn::STATUS_FAILED];
        if (self::isUpsellsAvailable($order, $order_main_txn['payment_provider'], $card_token, $order_main_txn['payer_id'])) {
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
                    case PaymentProviders::APPMAX:
                        $handler = new AppmaxService($api);
                        $payment = $handler->payByToken(
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
                        break;
                    case PaymentProviders::EBANX:
                        $handler = new EbanxService($api);
                        $payment = $handler->payByToken(
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
                        break;
                    case PaymentProviders::CHECKOUTCOM:
                        $handler = new CheckoutDotComService($api);
                        $payment = $handler->payByToken(
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
                        break;
                    case PaymentProviders::MINTE:
                        $handler = new MinteService($api);
                        $payment = $handler->payByToken(
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
                                'order_id'  => $order->getIdAttribute(),
                                'order_number'  => $order->number,
                                'user_agent'    => $user_agent,
                                'descriptor'    => $order->billing_descriptor
                            ]
                        );
                        if ($payment['status'] === Txn::STATUS_CAPTURED) {
                            $capture = $handler->capture($payment['hash'], ['amount' => $checkout_price, 'currency' => $order->currency]);
                            PaymentService::logTxn(array_merge($capture, ['payment_method' => $order_main_txn['payment_method']]));
                            $payment['status'] = $capture['status'];
                            $payment['capture_hash'] = $capture['hash'];
                        }
                        break;
                    case PaymentProviders::BLUESNAP:
                        $handler = new BluesnapService($api);
                        $payment = $handler->payByVaultedShopperId(
                            $order_main_txn['payer_id'],
                            [
                                'amount'    => $checkout_price,
                                'currency'  => $order->currency,
                                'billing_descriptor' => $order->billing_descriptor
                            ]
                        );
                        break;
                    case PaymentProviders::STRIPE:
                        $handler = new StripeService($api);
                        $payment = $handler->payBySavedCard(
                            $order_main_txn['payer_id'],
                            [
                                'amount' => CurrencyService::roundValueByCurrencyRules($checkout_price, $order->currency),
                                'currency' => $order->currency,
                                'order_id' => $order->getIdAttribute(),
                                'order_number' => $order->number,
                                'installments' => $order->installments,
                                'billing_descriptor' => $order->billing_descriptor
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
            }
        }

        return PaymentService::generateCreateUpsellsOrderResult($order, $upsells, $payment);
    }

    /**
     * Resolves Blusnap 3ds payment
     * @param string $order_id
     * @param string $ref
     * @return array
     * @throws OrderUpdateException
     * @throws \App\Exceptions\InvalidParamsException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
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
                PaymentService::addTxnToOrder($order, $payment, array_merge($order_txn, ['is_fallback' => true]));
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
     * @param PaymentCardMinte3dsRequest $req
     * @param string $order_id
     * @return array
     * @throws AuthException
     * @throws OrderUpdateException
     * @throws \App\Exceptions\InvalidParamsException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     * @throws \Exception
     */
    public static function minte3ds(PaymentCardMinte3dsRequest $req, string $order_id): array
    {
        $input      = $req->all();
        $sign       = $req->input('signature');
        $txn_hash   = $req->input('transid');
        $txn_ts     = $req->input('timestamp', '') ?? '';

        $order = OdinOrder::getById($order_id); // throwable
        $order_txn = $order->getTxnByHash($txn_hash); // throwable

        $minte = new MinteService(PaymentApiService::getById($order_txn['payment_api_id']));

        if (!$minte->verifySignature($txn_hash, $sign, $txn_ts)) {
            logger()->warning('Mint-e unauthorized redirect', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $result = ['status' => PaymentService::STATUS_FAIL];

        $payment = $minte->handle3ds($order_txn, $input);
        if ($payment['status'] === Txn::STATUS_CAPTURED) {
            if ($order_txn['status'] !== Txn::STATUS_APPROVED) {
                $capture = $minte->capture($payment['hash'], ['amount' => $order_txn['value'], 'currency' => $order->currency]);
                PaymentService::logTxn(array_merge($capture, ['payment_method' => $order_txn['payment_method']]));
                $payment['status'] = $capture['status'];
                $payment['capture_hash'] = $capture['hash'];
                if ($capture['status'] === Txn::STATUS_APPROVED) {
                    $result = ['status' => PaymentService::STATUS_OK];
                    PaymentService::approveOrder($payment, PaymentProviders::MINTE);
                }
            } else {
                $result = ['status' => PaymentService::STATUS_OK];
                logger()->warning("Minte3ds re-redirect approved [{$order_txn['hash']}]");
            }
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
                    PaymentService::addTxnToOrder($order, $reply['payment'], array_merge($order_txn, ['is_fallback' => true]));
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
     * Stripe 3ds redirect
     * @param string $order_id
     * @param string $pi_id
     * @return array
     * @throws OrderUpdateException
     * @throws PaymentException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     * @throws \Exception
     */
    public static function stripe3ds(string $order_id, string $pi_id): array
    {
        $order = OdinOrder::getById($order_id); // throwable
        $order_txn = $order->getTxnByHash($pi_id); // throwable
        $order_product = $order->getProductByTxnHash($order_txn['hash']); // throwable

        $stripe = new StripeService(PaymentApiService::getById($order_txn['payment_api_id']));

        $pinfo = $stripe->getPaymentInfo($pi_id);

        $result = ['status' => false, 'result' => false, 'is_main' => $order_product['is_main']];
        if ($pinfo['status']) {
            $result['status'] = true;
            switch ($pinfo['txn']['status']):
                case Txn::STATUS_APPROVED:
                    PaymentService::approveOrder($pinfo['txn'], PaymentProviders::STRIPE);
                case Txn::STATUS_AUTHORIZED:
                case Txn::STATUS_CAPTURED:
                    $result['result'] = true;
                    break;
                case Txn::STATUS_FAILED:
                    PaymentService::rejectTxn($pinfo['txn'], PaymentProviders::STRIPE);
                    PaymentService::cacheOrderErrors(array_merge($pinfo['txn'], ['number' => $order->number]));
                    break;
            endswitch;
        } else {
            PaymentService::cacheOrderErrors(array_merge($pinfo['txn'], ['number' => $order->number]));
        }

        return $result;
    }

    /**
     * Approves order by ebanx hashes
     * @param array $hashes
     * @return void
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
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

            $payment = $ebanx->requestStatusByHash($hash);

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
     * @param array $data
     * @return void
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
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
     * @param string $order_id
     * @param string $txn_hash
     * @return bool
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
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
     * @param string $order_id
     * @param string $txn_hash
     * @return bool
     * @throws OrderUpdateException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
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
     * @param string $number
     * @param string $hash
     * @return CheckoutDotComService
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
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
     * @param string $number
     * @param string $hash
     * @return BluesnapService
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
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
     * @throws \Exception
     */
    public static function setCardToken(string $order_number, ?string $token): void
    {
        if ($token) {
            $dt = (new \DateTime())->add(new \DateInterval("PT" . self::CACHE_TOKEN_TTL_MIN . "M"));
            Cache::put(self::CACHE_TOKEN_PREFIX . $order_number, $token, $dt);
        }
    }

    /**
     * Handles stripe webhook
     * @param string $sign
     * @param string $payload
     * @return void
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     * @throws OrderUpdateException
     */
    public static function stripeWebhook(string $sign, string $payload): void
    {
        $pi = StripeService::extractPaymentIntent($payload);
        if (!empty($pi)) {
            $order = OdinOrder::getByTxnHash($pi->id, PaymentProviders::STRIPE); //throwable
            $order_txn = $order->getTxnByHash($pi->id); // throwable

            $stripe = new StripeService(PaymentApiService::getById($order_txn['payment_api_id']));
            $reply = $stripe->validateWebhook($sign, $payload);

            if ($reply['status']) {
                PaymentService::approveOrder($reply['txn'], PaymentProviders::STRIPE);
            }
        }
    }
}
