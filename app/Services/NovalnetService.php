<?php

namespace App\Services;

use App\Models\PaymentApi;
use App\Models\Txn;
use App\Mappers\NovalnetAmountMapper;
use App\Mappers\NovalnetCodeMapper;
use App\Constants\PaymentProviders;
use App\Constants\PaymentMethods;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;

/**
 * Class NovalnetService
 * @package App\Services
 */
class NovalnetService extends ProviderService
{
    const ST_API_SUCCESS = 'SUCCESS';
    const ST_API_FAILURE = 'FAILURE';

    const ST_TXN_SUCCESS    = 'SUCCESS';
    const ST_TXN_CONFIRMED  = 'CONFIRMED';
    const ST_TXN_FAILURE    = 'FAILURE';
    const ST_TXN_PENDING    = 'PENDING';
    const ST_TXN_ON_HOLD    = 'ON_HOLD';

    const T_EVENT_UPDATE    = 'UPDATE';
    const T_EVENT_PAYMENT   = 'PAYMENT';

    const COUNTRY_CURRENCY = [
        'at' => ['EUR'],
        'de' => ['EUR'],
        'nl' => ['EUR'],
        'pl' => ['PLN']
    ];

    const METHODS = [
        PaymentMethods::EPS     => 'EPS',
        PaymentMethods::P24     => 'PRZELEWY24',
        PaymentMethods::IDEAL   => 'IDEAL',
        PaymentMethods::SEPA    => 'DIRECT_DEBIT_SEPA'
    ];

    /**
     * @var string
     */
    private string $endpoint = 'https://payport.novalnet.de/v2/';

    /**
     * @var \GuzzleHttp\Client
     */
    private \GuzzleHttp\Client $client;

    /**
     * MinteService constructor
     * @param PaymentApi $api
     */
    public function __construct(PaymentApi $api)
    {
        parent::__construct($api);
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->endpoint,
            'headers' => [
                'Content-Type' => 'application/json',
                'charset' => 'utf-8',
                'Accept' => 'application/json',
                'WWW-Authenticate' => 'Basic:' . base64_encode($this->api->secret)
            ]
        ]);
    }

    /**
     * Checks if the currency is supported
     * @param  string $country
     * @param  string $currency
     * @return bool
     */
    public static function isCurrencySupported(string $country, string $currency): bool
    {
        $result = false;
        if (isset(self::COUNTRY_CURRENCY[$country])) {
            $result = in_array($currency, self::COUNTRY_CURRENCY[$country]);
        }
        return $result;
    }

    /**
     * Returns Txn status from response
     * @param array $response
     * @return string
     */
    public static function mapResponseStatus(array $response): string
    {
        $st_api = Arr::get($response, 'result.status', self::ST_API_FAILURE);

        $result = Txn::STATUS_FAILED;
        if ($st_api === self::ST_API_SUCCESS) {
            $st_txn = Arr::get($response, 'transaction.status');
            switch ($st_txn):
                case self::ST_TXN_PENDING:
                    $result = Txn::STATUS_AUTHORIZED;
                    break;
                case self::ST_TXN_ON_HOLD:
                    $result = Txn::STATUS_CAPTURED;
                    break;
                case self::ST_TXN_CONFIRMED:
                case self::ST_TXN_SUCCESS:
                    $result = Txn::STATUS_APPROVED;
                    break;
                case null:
                    $t_sec = Arr::get($response, 'transaction.txn_secret');
                    $r_url = Arr::get($response, 'result.redirect_url');
                    if ($t_sec && $r_url) {
                        $result = Txn::STATUS_AUTHORIZED;
                    }
                    break;
                default:
                    $result = Txn::STATUS_FAILED;
            endswitch;
        }

        return $result;
    }

    /**
     * Creates Merchant object
     * @return array
     */
    public function createMerchantObj(): array
    {
        return ['tariff' => $this->api->login, 'signature' => $this->api->password];
    }

    /**
     * Creates Customer object
     * @param array $contacts
     * @return array
     */
    public function createCustomerObj(array $contacts): array
    {
        return [
            'first_name'  => $contacts['first_name'],
            'last_name'   => $contacts['last_name'],
            'email'       => $contacts['email'],
            'customer_ip' => $contacts['ip'],
            'tel'         => is_array($contacts['phone']) ? implode('', $contacts['phone']) : $contacts['phone'],
            'billing' => [
                'street'        => $contacts['street'],
                'city'          => $contacts['city'],
                'zip'           => $contacts['zip'],
                'country_code'  => $contacts['country'],
                'house_no'      => $contacts['building'] ?? null
            ],
            'shipping' => ['same_as_billing' => 1]
        ];
    }

    /**
     * Returns transaction object
     * @param string $method
     * @param array $contacts
     * @param array $details
     * @return array
     */
    public function createTransactionObject(string $method, array $contacts, array $details): array
    {
        $hook_url = 'https://' . request()->getHttpHost() . "/novalnet-webhook/{$details['order_id']}";
        $return_url = 'https://' . request()->getHttpHost() . "/novalnet-ret-cli/{$details['order_id']}";

        $result = [
            'payment_type' => self::METHODS[$method],
            'amount' => (int)NovalnetAmountMapper::toProvider($details['amount'], $details['currency']),
            'currency'  => $details['currency'],
            'test_mode' => \App::environment() !== 'production' ? 1 : 0,
            'order_no'  => $details['order_number'],
            'return_url' => $return_url,
            'error_return_url' => $return_url,
            'hook_url' => $hook_url
        ];

        if ($method === PaymentMethods::SEPA) {
            $result['payment_data'] = [
                'iban' => $contacts['document_number'],
                'account_holder' => "{$contacts['first_name']} {$contacts['last_name']}"
            ];
        }

        return $result;
    }

    /**
     * Creates payment response template
     * @param  array $details ['currency'=>string,'amount'=>float]
     * @return array
     */
    public function createPaymentTmpl(array $details): array
    {
        return [
            'is_flagged'        => false,
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_NEW,
            'payment_provider'  => PaymentProviders::NOVALNET,
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'payer_id'          => null,
            'provider_data'     => null,
            'errors'            => null
        ];
    }

    /**
     * Processes payment
     * @param  string $method
     * @param  array  $contacts
     * @param  array  $details
     * [
     *      'amount' => float,
     *      'currency' => string,
     *      'order_number' => string
     * ]
     * @return array
     */
    public function pay(string $method, array $contacts, array $details): array
    {
        $result = $this->createPaymentTmpl($details);

        try {

            $body = [
                'merchant' => $this->createMerchantObj(),
                'customer' => $this->createCustomerObj($contacts),
                'transaction' => $this->createTransactionObject($method, $contacts, $details)
            ];

            logger()->info('Novalnet req', $body);

            $res = $this->client->post('payment', ['json' => $body]);

            logger()->info('Novalnet res -> ' . $res->getBody());

            $body = json_decode($res->getBody(), true);

            $result['provider_data'] = ['code' => $res->getStatusCode(), 'body' => $body];
            $result['hash'] = Arr::get($body, 'transaction.tid') ?? ('fail_' . hrtime(true));
            $result['status'] = self::mapResponseStatus($body);

            if ($result['status'] === Txn::STATUS_AUTHORIZED) {
                $hash = Arr::get($body, 'transaction.tid');
                $result['hash'] = $hash ?? ('tsec_' . Arr::get($body, 'transaction.txn_secret'));
                $result['redirect_url'] = Arr::get($body, 'result.redirect_url');
            } else {
                logger()->warning("Novalnet pay", $result['provider_data']);
                $result['errors'] = [NovalnetCodeMapper::toPhrase(Arr::get($body, 'result.status_code'))];
            }
        } catch (RequestException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;
            $result['hash'] = 'fail_' . hrtime(true);
            $result['errors'] = [NovalnetCodeMapper::toPhrase()];
            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            logger()->warning("Novalnet pay", $result['provider_data']);
        }
        return $result;
    }

    /**
     * Returns payment
     * @param string $tid
     * @param string $currency
     * @param float $amount
     * @return array
     */
    public function getPayment(string $tid, string $currency, float $amount): array
    {
        $result = $this->createPaymentTmpl(compact('currency', 'amount'));

        $result['hash'] = $tid;

        try {
            $res = $this->client->post('transaction/details', ['json' => ['transaction' => ['tid' => $tid]]]);

            logger()->info('Novalnet t details', ['res' => $res->getBody()]);

            $body = json_decode($res->getBody(), true);

            $result['provider_data'] = ['code' => $res->getStatusCode(), 'body' => $body];
            $result['status'] = self::mapResponseStatus($body);

            if ($result['status'] === Txn::STATUS_FAILED) {
                logger()->warning("Novalnet txn details", $result['provider_data']);
                $result['errors'] = [NovalnetCodeMapper::toPhrase(Arr::get($body, 'result.status_code'))];
            }
        } catch (RequestException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;
            $result['errors'] = [NovalnetCodeMapper::toPhrase()];
            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            logger()->warning("Novalnet txn details", $result['provider_data']);
        }
        return $result;
    }

    /**
     * Generates notification signature
     * @param array $params
     * @return string
     */
    public function generateSignature(array $params): string
    {
        return hash('sha256', implode('', $params) . strrev($this->api->secret));
    }

    /**
     * Verifies webhook signature
     * @param array $notification
     * @return bool
     */
    public function verifyWebhook(array $notification): bool
    {
        $tid = Arr::get($notification, 'event.tid', '');
        $type = Arr::get($notification, 'event.type', '');
        $status = Arr::get($notification, 'result.status', '');
        $amount = Arr::get($notification, 'transaction.amount', '');
        $currency = Arr::get($notification, 'transaction.currency', '');
        $checksum = Arr::get($notification, 'event.checksum');

        return $checksum === $this->generateSignature([$tid, $type, $status, $amount, $currency]);
    }

    /**
     * Verifies return client signature
     * @param array $params
     * @return bool
     */
    public function verifyRetCli(array $params): bool
    {
        return $params['checksum'] === $this->generateSignature([$params['tid'], $params['tsec']]);
    }

    /**
     * Verifies webhook event
     * @param array $notification
     * @return bool
     */
    public function verifyWebhookEvent(array $notification): bool
    {
        return in_array(Arr::get($notification, 'event.type'), [self::T_EVENT_UPDATE, self::T_EVENT_PAYMENT]);
    }
}
