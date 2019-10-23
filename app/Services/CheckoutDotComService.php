<?php

namespace App\Services;

use App\Models\OdinOrder;
use App\Models\Setting;
use App\Models\Txn;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Mappers\CheckoutDotComCodeMapper;
use App\Mappers\CheckoutDotComAmountMapper;
use Checkout\CheckoutApi;
use Checkout\Models\Tokens\Card;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\Source;
use Checkout\Models\Payments\CardSource;
use Checkout\Models\Payments\TokenSource;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Checkout\Library\Exceptions\CheckoutException;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;
/**
 * CheckoutDotComService class
 */
class CheckoutDotComService
{
    const ENV_LIVE      = 'live';
    const ENV_SANDBOX   = 'sandbox';

    const STATUS_AUTHORIZED     = 'Authorized';
    const STATUS_PENDING        = 'Pending';
    const STATUS_CAPTURED       = 'Captured';
    const STATUS_DECLINED       = 'Declined';
    const STATUS_PAID           = 'Paid';
    const STATUS_CARD_VERIFIED  = 'Card Verified';

    const SUCCESS_CODE          = '10000';
    const SUCCESS_FLAGGED_CODE  = '10100';

    const TYPE_WEBHOOK_CAPTURED = 'payment_captured';

    const REPORTING_API_URL = 'https://api.checkout.com/reporting/';

    /**
     * @var string
     */
    private $secret_key;

    /**
     * @var string
     */
    private $webhook_secret_keys = [];

    /**
     * @var string
     */
    private $public_key;

    /**
     * @var string
     */
    private $env = self::ENV_LIVE;

    /**
     * @var CheckoutApi
     */
    private $checkout;

    /**
     * CheckoutDotComService constructor
     */
    public function __construct()
    {
        $this->secret_key = Setting::getValue('checkout_dot_com_secret_key');

        if (!$this->secret_key) {
            logger()->error("Checkout.com secret_key not found");
        }

        $this->public_key = Setting::getValue('checkout_dot_com_public_key');

        if (!$this->public_key) {
            logger()->error("Checkout.com public_key not found");
        }

        $webhook_secret_key = Setting::getValue('cdc_webhook_secret');
        $this->webhook_secret_keys[self::TYPE_WEBHOOK_CAPTURED] = $webhook_secret_key;

        if (!$webhook_secret_key) {
            logger()->error("cdc_webhook_secret not found");
        }

        $env = Setting::getValue('checkout_dot_com_api_env', self::ENV_LIVE);

        $this->env = $env;

        $this->checkout = new CheckoutApi($this->secret_key, $env === self::ENV_SANDBOX);
        $this->checkout->configuration()->setPublicKey($this->public_key);
    }

    /**
     * Returns checkout.com CardSource
     * @param  array      $card
     * @param  array      $contact
     * @return CardSource
     */
    public static function createCardSource(array $card, array $contact): CardSource
    {
        $source = new CardSource($card['number'], $card['month'], $card['year']);
        $source->cvv = $card['cvv'];
        $source->name = $contact['first_name'] . ' ' . $contact['last_name'];
        $source->phone = (object)[$contact['phone']];
        return $source;
    }

    /**
     * Returns checkout.com TokenSource
     * @param string $token
     * @return TokenSource
     */
    public static function createTokenSource(string $token): TokenSource
    {
        return new TokenSource($token);
    }

    /**
     * Creates a new payment
     * @param Source $source
     * @param array $contact
     * @param OdinOrder $order
     * @param bool|null $is3ds
     * @param float $amount default: $order->total_price
     * @return array
     */
    public function pay(Source $source, array $contact, OdinOrder $order, ?bool $is3ds, float $amount = null)
    {
        $amount = isset($amount) ? $amount : $order->total_price;

        $payment = new Payment($source, $order->currency);
        $payment->reference = $order->number;
        $payment->amount = CheckoutDotComAmountMapper::normalize($amount, $order->currency);
        $payment->description = 'Product Description';
        if (!empty($contact['payer_id'])) {
            $payment->customer = (object)['id' => $contact['payer_id']];
        } else {
            $payment->customer = (object)['email' => $contact['email'], 'name' => $contact['first_name'] . ' ' . $contact['last_name']];
            $payment->shipping = (object)[
                'address' => (object)[
                    'address_line1' => $contact['street'],
                    'city' => $contact['city'],
                    'country' => $contact['country'],
                    'state' => $contact['state'],
                    'zip' => $contact['zip']
                ],
                'phone' => (object)[$contact['phone']]
            ];
        }
        $payment->payment_ip = $order->ip;

        // enable 3ds
        if ($source instanceof CardSource) {
            $qs = http_build_query(['order' => $order->getIdAttribute()]);
            $payment->success_url = request()->getSchemeAndHttpHost() . PaymentService::SUCCESS_PATH . '?' . $qs . '&3ds=success';
            $payment->failure_url = request()->getSchemeAndHttpHost() . PaymentService::FAILURE_PATH . '?' . $qs . '&3ds=failure';
            $payment->{'3ds'} = (object)['enabled' => $is3ds];
        }

        $result = [
            'fee'               => 0,
            'is_flagged'        => false,
            'currency'          => $order->currency,
            'value'             => $amount,
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentService::PROVIDER_CHECKOUTCOM,
            'payment_method'    => PaymentService::METHOD_CREDITCARD,
            'hash'              => null,
            'payer_id'          => null,
            'provider_data'     => null,
            'redirect_url'      => null,
            'errors'            => null,
            'token'             => null
        ];

        // parse response
        try {
            $res = $this->checkout->payments()->request($payment);

            $result['provider_data'] = $res;
            $result['hash'] = $res->id;
            $result['payer_id'] = $res->customer['id'];

            if ($res->http_code === 201) { // authorized
                $response_code = (string)$res->response_code;
                $result['currency']         = $res->currency;
                $result['value']            = $res->amount;
                if (in_array($response_code, [self::SUCCESS_CODE, self::SUCCESS_FLAGGED_CODE])) {
                    $result['status']       = Txn::STATUS_CAPTURED;
                    $result['is_flagged']   = $response_code === self::SUCCESS_FLAGGED_CODE ? true : false;
                } else {
                    $result['errors']  = [CheckoutDotComCodeMapper::toPhrase($response_code)];
                }
            } elseif ($res->http_code === 202 && $res->status === self::STATUS_PENDING) { // pending 3ds
                $result['status']       = Txn::STATUS_NEW;
                $result['redirect_url'] = $res->_links['redirect']['href'];
            }
        } catch (CheckoutHttpException $ex) {
            $body = json_decode($ex->getBody(), true);
            if (!empty($body['request_id'])) {
                $result['hash'] = $body['request_id'];
            }
            $result['provider_data'] = $body;
            $result['errors'] = array_map(function($code) {
                return CheckoutDotComCodeMapper::toPhrase($code);
            },$ex->getErrors() ?? []);
            logger()->error("Checkout.com pay", ['code' => $ex->getCode(), 'body' => $body, 'req' => json_encode($payment->getValues())]);
        } catch (CheckoutException $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'body' => $ex->getBody()];
            logger()->error("Checkout.com pay", ['code' => $ex->getCode(), 'body' => $ex->getBody(), 'req' => json_encode($payment->getValues())]);
        }

        return $result;
    }

    /**
     * Returns fee by payment_id
     * @param  string $payment_id
     * @return float
     */
    public function requestFee(string $payment_id): float
    {
        // Reconciliation api isn't available in sandbox
        if ($this->env !== self::ENV_LIVE) {
            return 0.0;
        }

        $client = new GuzzHttpCli(['base_uri' => self::REPORTING_API_URL]);

        $res = null;
        $result = 0.0;
        try {
            $res = $client->request('GET', "payments/{$payment_id}", [
                'headers' => [
                    'Authorization' => $this->secret_key,
                    'Content-Type'  => 'application/json'
                ]
            ]);

            logger()->info('Checkout.com Reporting API status -> ' . $res->getStatusCode());
        } catch (GuzzReqException $e) {
            logger()->error("Checkout.com Reporting API [{$payment_id}]", [
                'request'   => Psr7\str($e->getRequest()),
                'response'  => $e->hasResponse() ? Psr7\str($e->getResponse()) : null,
            ]);
        }

        if (isset($res) && (int)$res->getStatusCode() === 200) {
            logger()->info('Checkout.com Reporting API body -> ' . $res->getBody());

            $body = \json_decode($res->getBody(), true);

            if (!empty($body['data']) && !empty($body['data']['actions'])) {
                foreach ($body['data']['actions'] as $action) {
                    $bds = $action['breakdown'] ?? [];
                    // sum negative items
                    foreach ($bds as $item) {
                        $fee = (float)($item['processing_currency_amount'] ?? 0.0);
                        if ($fee < 0) {
                            $result += ($fee * -1);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Tokenizes customer card
     * @param array $card
     * @param array $contact
     * @return string|null
     */
    public function requestToken(array $card, array $contact): ?string
    {
        $source = new Card($card['number'], $card['month'], $card['year']);
        $source->cvv = $card['cvv'];
        $source->name = $contact['first_name'] . ' ' . $contact['last_name'];
        $source->phone = (object)[$contact['phone']];

        $result = null;
        try {
            $card_token = $this->checkout->tokens()->request($source);
            $result = $card_token->token;
        } catch (CheckoutException $ex) {
            logger()->error("Checkout.com token", ['code' => $ex->getCode(), 'errors' => $ex->getErrors()]);
        }
        return $result;
    }

    /**
     * Validates webhook
     * @param array $data =['secret' => string, 'sign' => string, 'content' => string]
     * @param string $type
     * @return boolean
     */
    private function validateWebhook($data, $type = null): bool
    {
        if ($type) {
            $webhook_secret_key = $this->webhook_secret_keys[$type] ?? '';
            if ($data['secret'] !== $webhook_secret_key) {
                return false;
            }
        }

        $sign = hash_hmac('sha256', $data['content'], $this->secret_key);
        if ($data['sign'] === $sign) {
            return true;
        }
        return false;
    }

    /**
     * Validates captured webhook
     * @param  Request $req
     * @return array
     */
    public function validateCapturedWebhook(Request $req)
    {
        $secret = $req->header('authorization');
        $sign = $req->header('cko-signature');
        $content = $req->getContent();
        $type = $req->get('type') ?? self::TYPE_WEBHOOK_CAPTURED;
        $data = $req->get('data') ?? [];

        $result = ['status' => false];

        $is_valid = $this->validateWebhook(['secret' => $secret, 'sign' => $sign, 'content' => $content], $type);

        if ($is_valid && !empty($data)) {
            $result = [
                'status'    => true,
                'txn' => [
                    'status'    => Txn::STATUS_APPROVED,
                    'fee'       => $this->requestFee($data['id']),
                    'hash'      => $data['id'],
                    'number'    => $data['reference'],
                    'value'     => $data['amount'],
                    'currency'  => $data['currency']
                ]
            ];
        }

        return $result;
    }

    /**
     * Validates failed webhooks (payment_declined, payment_canceled, payment_capture_declined)
     * @param  Request $req
     * @return array
     */
    public function validateFailedWebhook(Request $req)
    {
        $secret = $req->header('authorization');
        $sign = $req->header('cko-signature');
        $content = $req->getContent();
        $data = $req->get('data') ?? [];

        $result = ['status' => false];

        $is_valid = $this->validateWebhook(['secret' => $secret, 'sign' => $sign, 'content' => $content]);

        if ($is_valid && !empty($data)) {
            $response_code = (string)$data['response_code'];
            $result = [
                'status'        => true,
                'order_number'  => $data['reference'],
                'errors'        => [CheckoutDotComCodeMapper::toPhrase($response_code)]
            ];
        }

        return $result;
    }

}
