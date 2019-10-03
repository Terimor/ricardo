<?php

namespace App\Services;

use App\Models\OdinOrder;
use App\Models\Setting;
use App\Models\Txn;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Checkout\CheckoutApi;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\Source;
use Checkout\Models\Payments\CardSource;
use Checkout\Models\Payments\TokenSource;
use Checkout\Models\Tokens\Card;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Checkout\Library\Exceptions\CheckoutException;
/**
 * CheckoutDotComService class
 */
class CheckoutDotComService
{
    const ENV_LIVE = 'live';
    const ENV_SANDBOX = 'sandbox';

    const STATUS_AUTHORIZED = 'Authorized';
    const STATUS_PENDING = 'Pending';
    const STATUS_CAPTURED = 'Captured';
    const STATUS_DECLINED = 'Declined';
    const STATUS_PAID = 'Paid';
    const STATUS_CARD_VERIFIED = 'Card Verified';

    const SUCCESS_CODE = 10000;
    const SUCCESS_FLAGGED_CODE = 10100;

    const TYPE_WEBHOOK_CAPTURED = 'payment_captured';

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
     * @var CheckoutApi
     */
    private $checkout;

    /**
     * CheckoutDotController constructor.
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

        $webhook_secret_key = Setting::getValue('checkout_dot_com_captured_webhook_secret_key');
        $this->webhook_secret_keys[self::TYPE_WEBHOOK_CAPTURED] = $webhook_secret_key;

        if (!$webhook_secret_key) {
            logger()->error("checkout_dot_com_captured_webhook_secret_key not found");
        }

        $env = Setting::getValue('checkout_dot_com_api_env', self::ENV_LIVE);

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
        $source->billing_address = (object)[
            'address_line1' => $contact['street'],
            'city' => $contact['city'],
            'country' => $contact['country'],
            'state' => $contact['state'],
            'zip' => $contact['zip']
        ];
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
     * Creates checkout 3ds object for payment
     * @param  string $card_type
     * @param  string $country
     * @param  array|null $ipqs
     * @return object
     */
    public static function create3dsObj(string $card_type, string $country, ?array $ipqs): object
    {
        $result = (object)['enabled' => false];

        $setting = PaymentService::$providers[PaymentService::PROVIDER_CHECKOUTCOM]['methods'][$card_type] ?? [];
        $fraud_chance = !empty($ipqs) ? (int)$ipqs['fraud_chance'] : PaymentService::FRAUD_CHANCE_MAX;

        if (in_array($country, $setting['+3ds'] ?? []) || $fraud_chance > PaymentService::FRAUD_CHANCE_LIMIT) {
            $result = (object)['enabled' => true];
        }
        return $result;
    }

    /**
     * Creates a new payment
     * @param Source $source
     * @param array $contact
     * @param OdinOrder $order
     * @param object|null $card_3ds
     * @param float $amount default: $order->total_price
     * @return array
     */
    public function pay(Source $source, array $contact, OdinOrder $order, ?object $card_3ds, float $amount = null)
    {
        $amount = isset($amount) ? $amount : $order->total_price;

        $payment = new Payment($source, $order->currency);
        $payment->reference = $order->number;
        $payment->amount = $amount;
        $payment->description = 'Product Description';
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
        $payment->payment_ip = $order->ip;

        $qs = http_build_query(array_merge(['order' => $order->getIdAttribute()], $order->params ?? []));
        $payment->success_url = request()->getSchemeAndHttpHost() . PaymentService::SUCCESS_PATH . '?' . $qs . '&3ds=success';
        $payment->failure_url = request()->getSchemeAndHttpHost() . PaymentService::FAILURE_PATH . '?' . $qs . '&3ds=failure';

        // enable 3ds
        if ($card_3ds) {
            $payment->{'3ds'} = $card_3ds;
        }

        $result = [
            'is_flagged'        => false,
            'hash'              => null,
            'currency'          => $order->currency,
            'value'             => $amount,
            'status'            => Txn::STATUS_FAILED,
            'fee'               => 0,
            'provider_data'     => null,
            'payment_provider'  => PaymentService::PROVIDER_CHECKOUTCOM,
            'payment_method'    => PaymentService::METHOD_CREDITCARD,
            'payer_id'          => null,
            'redirect_url'      => null
        ];

        // parse response
        try {
            $res = $this->checkout->payments()->request($payment);

            $result['provider_data'] = $res;
            $result['hash'] = $res->id;
            $result['payer_id'] = $res->customer['id'];

            if ($res->http_code === 201) { // authorized
                $result['currency'] = $res->currency;
                $result['value'] = $res->amount;
                $res_code = (int)$res->response_code;
                if ($res_code === self::SUCCESS_CODE || $res_code === self::SUCCESS_FLAGGED_CODE) {
                    $result['status'] = Txn::STATUS_CAPTURED;
                }
                $result['is_flagged'] = $res_code === self::SUCCESS_FLAGGED_CODE;
            } elseif ($res->http_code === 202 && $res->status === self::STATUS_PENDING) { // pending 3ds
                $result['status'] = Txn::STATUS_NEW;
                $result['redirect_url'] = $res->_links['redirect']['href'];
            }
        } catch (CheckoutHttpException $ex) {
            $body = json_decode($ex->getBody(), true);
            if (!empty($body['request_id'])) {
                $result['hash'] = $body['request_id'];
            }
            $result['provider_data'] = $body;
            logger()->error("Checkout.com pay", ['code' => $ex->getCode(), 'errors' => $ex->getErrors()]);
        } catch (CheckoutException $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'errors' => $ex->getErrors()];
            logger()->error("Checkout.com pay", ['code' => $ex->getCode(), 'errors' => $ex->getErrors()]);
        }

        return $result;
    }

    /**
     * Tokenizes customer card
     * @param  array  $card    [description]
     * @param  array  $contact [description]
     * @return string|null
     */
    public function requestToken(array $card, array $contact): ?string
    {
        $source = new Card($card['number'], $card['month'], $card['year']);
        $source->cvv = $card['cvv'];
        $source->name = $contact['first_name'] . ' ' . $contact['last_name'];
        $source->phone = (object)[$contact['phone']];
        $source->billing_address = (object)[
            'address_line1' => $contact['street'],
            'city' => $contact['city'],
            'country' => $contact['country'],
            'state' => $contact['state'],
            'zip' => $contact['zip']
        ];

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
                'hash'      => $data['id'],
                'number'    => $data['reference'],
                'value'     => $data['amount'],
                'currency'  => $data['currency']
            ];
        }

        return $result;
    }

}
