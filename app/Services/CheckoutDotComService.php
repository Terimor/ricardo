<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\PaymentApi;
use App\Models\Txn;
use App\Mappers\CheckoutDotComCodeMapper;
use App\Mappers\CheckoutDotComAmountMapper;
use App\Constants\PaymentProviders;
use Checkout\CheckoutApi;
use Checkout\Models\Address;
use Checkout\Models\Phone;
use Checkout\Models\Tokens\Card;
use Checkout\Models\Payments\Capture;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\Source;
use Checkout\Models\Payments\Voids;
use Checkout\Models\Payments\Refund;
use Checkout\Models\Payments\CustomerSource;
use Checkout\Models\Payments\CardSource;
use Checkout\Models\Payments\TokenSource;
use Checkout\Models\Payments\Shipping;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Checkout\Library\Exceptions\CheckoutException;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;

/**
 * Class CheckoutDotComService
 * @package App\Services
 */
class CheckoutDotComService extends ProviderService
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

    const REPORTING_API_URL = 'https://api.checkout.com/reporting/';

    /**
     * @var array
     */
    private static array $fallback_codes = ['20005', '20024', '20031', '20046', '200N0', '200T3', '20105'];

    /**
     * @var string
     */
    private string $env;

    /**
     * @var CheckoutApi
     */
    private CheckoutApi $checkout;

    /**
     * CheckoutDotComService constructor
     * @param PaymentApi $api
     */
     public function __construct(PaymentApi $api)
    {
        parent::__construct($api);
        $this->env = Setting::getValue('checkout_dot_com_api_env', self::ENV_LIVE);
        $this->checkout = new CheckoutApi($this->api->secret, $this->env === self::ENV_SANDBOX);
        $this->checkout->configuration()->setPublicKey($this->api->key);
    }

    /**
     * Returns checkout.com CardSource
     * @param  array $card
     * @param  array|null $contacts
     * @return CardSource
     */
    public static function createCardSource(array $card, ?array $contacts = null): CardSource
    {
        $src = new CardSource($card['number'], $card['month'], $card['year']);
        $src->cvv = $card['cvv'];
        if (!empty($contacts)) {
            if (is_array($contacts['phone'])) {
                $phone = new Phone();
                $phone->country_code = $contacts['phone']['country_code'];
                $phone->number = $contacts['phone']['number'];

                $src->phone = $phone;
            }

            $src->name = "{$contacts['first_name']} {$contacts['last_name']}";
        }
        return $src;
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
     * Returns checkout.com CustomerSource
     * @param string $id
     * @return array
     */
    public static function createCustomerSourceById(string $id): CustomerSource
    {
        return new CustomerSource($id);
    }

    /**
     * Returns checkout.com CustomerSource
     * @param string $email
     * @param string $fname
     * @param string $lname
     * @return CustomerSource
     */
    public static function createCustomerSourceByEmail(string $email, string $fname, string $lname): CustomerSource
    {
        $cus = new CustomerSource($email);
        $cus->name = "{$fname} {$lname}";
        return $cus;
    }

    /**
     * Returns Shipping Model
     * @param array $contacts
     * @return Shipping
     */
    public static function createShippingSource(array $contacts): Shipping
    {
        $address = new Address();
        $address->address_line1 = $contacts['street'];
        $address->city = $contacts['city'];
        $address->country = $contacts['country'];
        $address->state = $contact['state'] ?? '';
        $address->zip = $contacts['zip'];

        $phone = null;
        if (is_array($contacts['phone'])) {
            $phone = new Phone();
            $phone->country_code = $contacts['phone']['country_code'];
            $phone->number = $contacts['phone']['number'];
        }

        return new Shipping($address, $phone);
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
            logger()->warning("Checkout.com Reporting API [{$payment_id}]", [
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
     * @param array $contacts
     * @return string|null
     */
    public function requestToken(array $card, array $contacts): ?string
    {
        $source = new Card($card['number'], $card['month'], $card['year']);
        $source->cvv = $card['cvv'];
        $source->name = "{$contacts['first_name']} {$contacts['last_name']}";
        $source->phone = (object)[$contacts['phone']];

        $result = null;
        try {
            $card_token = $this->checkout->tokens()->request($source);
            $result = $card_token->token;
        } catch (CheckoutException $ex) {
            logger()->warning("Checkout.com token", ['code' => $ex->getCode(), 'errors' => $ex->getErrors()]);
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
            logger()->warning("Checkout.com capture", ['code' => $ex->getCode(), 'body' => $ex->getBody()]);
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
            logger()->warning("Checkout.com void", ['code' => $ex->getCode(), 'body' => $ex->getBody()]);
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
            logger()->warning("Checkout.com refund", ['code' => $ex->getCode(), 'body' => $ex->getBody()]);

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
     * @param array $card
     * @param array $contacts
     * @param array $details=[
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
    public function payByCard(array $card, array $contacts, array $details): array
    {
        return $this->pay(
            self::createCardSource($card, $contacts),
            self::createCustomerSourceByEmail($contacts['email'], $contacts['first_name'], $contacts['last_name']),
            self::createShippingSource($contacts),
            array_merge($details, ['ip' => $contacts['ip']])
        );
    }

    /**
     * Creates a new payment by card for existing customer
     * @param array $card
     * @param string $payer_id
     * @param array $contacts
     * @param array $details =[
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
    public function payByCardAndPayerId(array $card, string $payer_id, array $contacts, array $details): array
    {
        return $this->pay(
            self::createCardSource($card, $contacts),
            self::createCustomerSourceById($payer_id),
            self::createShippingSource($contacts),
            array_merge($details, ['ip' => $contacts['ip']])
        );
    }

    /**
     * Creates a new payment by token
     * @param string $token
     * @param array $contacts
     * @param array $details=[
     *                  'amount'=>float,
     *                  'currency'=>string,
     *                  'billing_descriptor'=>['name'=>string,'city'=>string]
     *                  'description'=>string,
     *                  'id'=>string,
     *                  'number'=>string
     *              ]
     */
    public function payByToken(string $token, array $contacts, array $details): array
    {
        return $this->pay(self::createTokenSource($token), $contacts, $details);
    }

    /**
     * Creates a new payment
     * @param Source $source
     * @param CustomerSource $customer
     * @param Shipping|null $shipping
     * @param array $details
     * @return array
     */
    private function pay(Source $source, CustomerSource $customer, Shipping $shipping, array $details): array
    {
        $payment = new Payment($source, $details['currency']);
        $payment->reference = $details['number'];
        $payment->amount = CheckoutDotComAmountMapper::toProvider($details['amount'], $details['currency']);
        $payment->description = $details['description'];
        $payment->billing_descriptor = (object)$details['billing_descriptor'];
        $payment->customer = $customer;
        $payment->shipping = $shipping;
        $payment->payment_ip = $details['ip'];

        // enable 3ds
        if ($source instanceof CardSource) {
            $qs = http_build_query(['order' => $details['id']]);
            $payment->success_url = 'https://' . request()->getHttpHost() . PaymentService::SUCCESS_PATH . '?' . $qs . '&3ds=success';
            $payment->failure_url = 'https://' . request()->getHttpHost() . PaymentService::FAILURE_PATH . '?' . $qs . '&3ds=failure';
            $payment->{'3ds'} = (object)['enabled' => $details['3ds'] ?? false];
        }

        $result = [
            'payment_provider' => PaymentProviders::CHECKOUTCOM,
            'payment_api_id' => (string)$this->api->getIdAttribute(),
            'currency' => $details['currency'],
            'status' => Txn::STATUS_FAILED,
            'value' => $details['amount'],
            'hash' => 'fail_' . hrtime(true),
            'errors' => null,
            'fallback' => false,
            'payer_id' => null,
            'is_flagged' => false,
            'redirect_url' => null,
            'provider_data' => null
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
            logger()->warning("Checkout.com pay", [
                'order' => $details['number'],
                'code' => $ex->getCode(),
                'body' => $ex->getBody()
            ]);
        } catch (CheckoutException $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'body' => $ex->getBody()];
            $result['errors'] = [CheckoutDotComCodeMapper::toPhrase()];
            logger()->warning("Checkout.com pay", [
                'order' => $details['number'],
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
