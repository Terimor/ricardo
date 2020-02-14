<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\PaymentApi;
use App\Models\Txn;
use App\Services\PaymentService;
use App\Mappers\CheckoutDotComCodeMapper;
use App\Mappers\CheckoutDotComAmountMapper;
use App\Constants\PaymentMethods;
use App\Constants\PaymentProviders;
use Checkout\CheckoutApi;
use Checkout\Library\Model;
use Checkout\Models\Tokens\Card;
use Checkout\Models\Payments\Capture;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\Source;
use Checkout\Models\Payments\Voids;
use Checkout\Models\Payments\Refund;
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
    use \App\Services\ProviderServiceTrait;

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

    const REPORTING_API_URL = 'https://api.checkout.com/reporting/';

    /**
     * @var array
     */
    private static $fallback_codes = ['20005','20024','20031','20046','200N0','200T3','20105'];

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
     * @param PaymentApi $api
     */
     public function __construct(PaymentApi $api)
    {
        $this->env = Setting::getValue('checkout_dot_com_api_env', self::ENV_LIVE);
        $this->api = $api;
        $this->checkout = new CheckoutApi($this->api->secret, $this->env === self::ENV_SANDBOX);
        $this->checkout->configuration()->setPublicKey($this->api->key);
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
                    'Authorization' => $this->checkout->configuration()->getSecretKey(),
                    'Content-Type'  => 'application/json'
                ]
            ]);
        } catch (GuzzReqException $e) {
            logger()->error("Checkout.com Reporting API [{$payment_id}]", [
                'request'   => Psr7\str($e->getRequest()),
                'response'  => $e->hasResponse() ? Psr7\str($e->getResponse()) : null,
            ]);
        }

        if (isset($res) && (int)$res->getStatusCode() === 200) {
            $body = \json_decode($res->getBody(), true);
            $data = !empty($body['data']) ? array_pop($body['data']) : [];

            if (!empty($data['actions'])) {
                foreach ($data['actions'] as $action) {
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
     * Captures payment
     * @param  string $id
     * @return bool
     */
    public function capture(string $id): bool
    {
        $result = false;
        try {
            $res = $this->checkout->payments()->capture(new Capture($id));
            $result = $res->http_code === 202 ? true : false;
        } catch (CheckoutException $ex) {
            logger()->error("Checkout.com capture", ['code' => $ex->getCode(), 'body' => $ex->getBody()]);
        }
        return $result;
    }

    /**
     * Voids payment
     * @param  string $id
     * @return bool
     */
    public function void(string $id): bool
    {
        $result = false;
        try {
            $res = $this->checkout->payments()->void(new Voids($id));
            $result = $res->http_code === 202 ? true : false;
        } catch (CheckoutException $ex) {
            logger()->error("Checkout.com void", ['code' => $ex->getCode(), 'body' => $ex->getBody()]);
        }
        return $result;
    }

    /**
     * Voids payment
     * @param  string $hash
     * @param  string $order_number
     * @param  string $currency
     * @param  float|null  $amount default=null
     * @return array
     */
    public function refund(string $hash, string $order_number, string $currency, ?float $amount = null): array
    {
        $result = ['status' => false];
        try {
            $amount = $amount ? CheckoutDotComAmountMapper::toProvider($amount, $currency) : null;
            $res = $this->checkout->payments()->refund(new Refund($hash, $amount, $order_number));
            if ($res->http_code === 202) {
                $result['status'] = true;
            }
        } catch (CheckoutException $ex) {
            logger()->error("Checkout.com refund", ['code' => $ex->getCode(), 'body' => $ex->getBody()]);

            switch ($ex->getCode()):
                case 422:
                    $result['errors'][] = "Invalid data was sent [{$hash}]";
                    break;
                case 403:
                    $result['errors'][] = "Refund not allowed [{$hash}]";
                    break;
                case 404:
                    $result['errors'][] = "Payment not found [{$hash}]";
                    break;
                default:
                    $result['errors'][] = "Something went wrong {{$hash}}";
            endswitch;
        }
        return $result;
    }

    /**
     * Creates a new payment by card
     * @param  array $card
     * @param  array $contact
     * @param  array $order_details=[
     *                  'amount'=>float,
     *                  'currency'=>string,
     *                  'billing_descriptor'=>['name'=>string,'city'=>string]
     *                  'description'=>string,
     *                  'id'=>string,
     *                  'number'=>string,
     *                  '3ds'=>?bool
     *              ]
     * @return array
     */
    public function payByCard(array $card, array $contact, array $order_details): array
    {
        return $this->pay(self::createCardSource($card, $contact), $contact, $order_details);
    }

    /**
     * Creates a new payment by token
     * @param  string $token
     * @param  array  $contact
     * @param  array  $order_details
     * @param  array $order_details=[
     *                  'amount'=>float,
     *                  'currency'=>string,
     *                  'billing_descriptor'=>['name'=>string,'city'=>string]
     *                  'description'=>string,
     *                  'id'=>string,
     *                  'number'=>string
     *              ]
     */
    public function payByToken(string $token, array $contact, array $order_details): array
    {
        return $this->pay(self::createTokenSource($token), $contact, $order_details);
    }

    /**
     * Creates a new payment
     * @param Source $source
     * @param array $contact
     * @param array $order_details
     * @return array
     */
    private function pay(Source $source, array $contact, array $order_details): array
    {
        $payment = new Payment($source, $order_details['currency']);
        $payment->reference = $order_details['number'];
        $payment->amount = CheckoutDotComAmountMapper::toProvider($order_details['amount'], $order_details['currency']);
        $payment->description = $order_details['description'];
        $payment->billing_descriptor = (object)$order_details['billing_descriptor'];
        if (!empty($contact['payer_id'])) {
            $payment->customer = (object)['id' => $contact['payer_id']];
        } else {
            $payment->customer = (object)['email' => $contact['email'], 'name' => $contact['first_name'] . ' ' . $contact['last_name']];
            $payment->shipping = (object)[
                'address' => (object)[
                    'address_line1' => $contact['street'],
                    'city' => $contact['city'],
                    'country' => $contact['country'],
                    'state' => $contact['state'] ?? '',
                    'zip' => $contact['zip']
                ],
                'phone' => (object)[$contact['phone']]
            ];
        }
        $payment->payment_ip = $contact['ip'];

        // enable 3ds
        if ($source instanceof CardSource) {
            $qs = http_build_query(['order' => $order_details['id']]);
            $payment->success_url = 'https://' . request()->getHttpHost() . PaymentService::SUCCESS_PATH . '?' . $qs . '&3ds=success';
            $payment->failure_url = 'https://' . request()->getHttpHost() . PaymentService::FAILURE_PATH . '?' . $qs . '&3ds=failure';
            $payment->{'3ds'} = (object)['enabled' => $order_details['3ds'] ?? false];
        }

        $result = [
            'fallback'          => false,
            'is_flagged'        => false,
            'currency'          => $order_details['currency'],
            'value'             => $order_details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::CHECKOUTCOM,
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'hash'              => "fail_" . UtilsService::randomString(16),
            'payer_id'          => null,
            'provider_data'     => null,
            'redirect_url'      => null,
            'errors'            => null,
            'token'             => null
        ];

        // parse response
        try {
            ini_set('serialize_precision', 15);

            $res = $this->checkout->payments()->request($payment);

            $result['provider_data'] = $res;
            $result['hash'] = $res->id;
            $result['payer_id'] = $res->customer['id'];

            if ($res->http_code === 201) { // authorized
                $response_code = (string)$res->response_code;
                $result['currency'] = $res->currency;
                $result['value']    = CheckoutDotComAmountMapper::fromProvider((int)$res->amount, $res->currency);

                if ($response_code === self::SUCCESS_CODE) {
                    $result['status']   = Txn::STATUS_CAPTURED;
                } elseif ($response_code === self::SUCCESS_FLAGGED_CODE) {
                    $result['status']       = Txn::STATUS_AUTHORIZED;
                    $result['is_flagged']   = true;
                } else {
                    $result['fallback'] = in_array($response_code, self::$fallback_codes);
                    $result['errors']   = [CheckoutDotComCodeMapper::toPhrase($response_code)];
                }
            } elseif ($res->http_code === 202 && $res->status === self::STATUS_PENDING) { // pending 3ds
                $result['status']       = Txn::STATUS_AUTHORIZED;
                $result['redirect_url'] = $res->_links['redirect']['href'];
            }
        } catch (CheckoutHttpException $ex) {
            $result['provider_data'] = $ex->getBody();
            $result['errors'] = array_map(function($code) {
                return CheckoutDotComCodeMapper::toPhrase($code);
            },$ex->getErrors() ?? []);
            logger()->error("Checkout.com pay", [
                'order' => $order_details['number'],
                'code' => $ex->getCode(),
                'body' => $ex->getBody()
            ]);
        } catch (CheckoutException $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'body' => $ex->getBody()];
            $result['errors'] = [CheckoutDotComCodeMapper::toPhrase()];
            logger()->error("Checkout.com pay", [
                'order' => $order_details['number'],
                'code' => $ex->getCode(),
                'body' => $ex->getBody()
            ]);
        }

        return $result;
    }

    /**
     * Validates webhook
     * @param array $data =['secret' => string, 'sign' => string, 'content' => string]
     * @return boolean
     */
    private function validateWebhook($data): bool
    {
        $sign = hash_hmac('sha256', $data['content'], $this->checkout->configuration()->getSecretKey());
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
        $data = $req->get('data') ?? [];

        $result = ['status' => false];

        $is_valid = $this->validateWebhook(['secret' => $secret, 'sign' => $sign, 'content' => $content]);

        if ($is_valid && !empty($data)) {
            $result = [
                'status'    => true,
                'txn' => [
                    'status'    => Txn::STATUS_APPROVED,
                    'hash'      => $data['id'],
                    'number'    => $data['reference'],
                    'value'     => CheckoutDotComAmountMapper::fromProvider((int)$data['amount'], $data['currency']),
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
                'status'    => true,
                'txn' => [
                    'hash'      => $data['id'],
                    'number'    => $data['reference'],
                    'status'    => Txn::STATUS_FAILED,
                    'fallback'  => in_array($response_code, self::$fallback_codes),
                    'errors'    => [CheckoutDotComCodeMapper::toPhrase($response_code)]
                ]
            ];
        }

        return $result;
    }

}
