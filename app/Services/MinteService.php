<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Currency;
use App\Models\PaymentApi;
use App\Models\Txn;
use App\Constants\PaymentMethods;
use App\Constants\PaymentProviders;
use App\Mappers\MinteCodeMapper;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;

/**
 * MinteService class
 */
class MinteService
{
    use ProviderServiceTrait;

    const ENV_LIVE      = 'live';
    const ENV_SANDBOX   = 'sandbox';

    const STATUS_OK     = 'SUCCESS';
    const STATUS_3DS    = 'PENDING';
    const STATUS_FAIL   = 'FAILED';

    const COUNTRY_CURRENCY = [
        'us' => ['USD'],
        '*'  => [
            'CAD', 'EUR', 'GBP', 'USD'
            // 'CAD', 'USD', 'EUR', 'GBP','CHF', 'SEK', 'DKK', 'NOK', 'JPY', 'KRW', 'AUD', 'NZD', 'ZAR', 'AED', 'PLN', 'CZK', 'HUF',
            // 'RON', 'BGN', 'TWD', 'HKD', 'SGD', 'INR', 'ILS', 'MYR', 'CLP', 'RUB', 'TRY', 'PHP', 'HRK', 'MXN', 'COP', 'ARS'
        ]
    ];

    /**
     * @var array
     */
    private static $fallback_codes = ['05', 'Amount by terminal exceeded', 'ERR-MPI'];

    /**
     * @var string
     */
    private $endpoint;


    /**
     * MinteService constructor
     */
    public function __construct()
    {
        $keys = PaymentApi::getAllByProvider(PaymentProviders::MINTE);

        if (empty($keys)) {
            logger()->error("Mint-e configuration needs to check");
        }

        $this->keys = $keys;

        $environment = Setting::getValue('mint_environment', self::ENV_LIVE);
        $this->endpoint = 'https://' . ($environment === self::ENV_LIVE ? 'prod' : 'test') . '.mint-e.com/process/v1.0/';
    }

    /**
     * Returns available currency by country
     * @param  string $country
     * @param  string|null $currency
     * @return string
     */
    public static function getCurrencyByCountry(string $country, ?string $currency): ?string
    {
        if (isset(self::COUNTRY_CURRENCY[$country])) {
            return in_array($currency, self::COUNTRY_CURRENCY[$country]) ? $currency : Currency::DEF_CUR;
        } elseif (in_array($currency, self::COUNTRY_CURRENCY['*'])) {
            return $currency;
        } else {
            return Currency::DEF_CUR;
        }
    }

    /**
     * Provides payment by card
     * @param  array   $card
     * @param  array   $contact
     * @param  array   $details
     * [
     *  '3ds'=>boolean,
     *  'currency'=>string,
     *  'amount'=>float,
     *  'order_id'=>string,
     *  'order_number'=>string,
     *  'product_id'=>string,
     *  'user_agent'=>string,
     *  'descriptor'=>string
     * ]
     * @return array
     */
    public function payByCard(array $card, array $contact, array $details): array
    {
        $phone = $contact['phone'];
        if (\gettype($contact['phone']) === 'array') {
            $phone = $contact['phone']['country_code'] . $contact['phone']['number'];
        }
        $payment = $this->authorize($card, array_merge($contact, ['phone' => $phone]), $details);
        if ($payment['status'] === Txn::STATUS_CAPTURED) {
            return $this->capture($payment);
        }
        return $payment;
    }

    /**
     * Provides payment by token
     * @param  string  $token
     * @param  array   $contact
     * @param  array   $details
     * [
     *  'currency'=>string,
     *  'amount'=>float,
     *  'order_id'=>string,
     *  'order_number'=>string,
     *  'user_agent'=>string,
     *  'descriptor'=>string
     *  'payment_api_id'=>string
     * ]
     * @return array
     */
    public function payByToken(string $token, array $contact, array $details): array
    {
        $cardjs = self::decrypt($token, $details['order_id']);

        $payment = $this->authorize(json_decode($cardjs, true), $contact, array_merge($details, ['3ds' => false]));

        if ($payment['status'] === Txn::STATUS_CAPTURED) {
            return $this->capture($payment);
        }

        return $payment;
    }

    /**
     * Authorizes payment
     * @param  array   $card
     * @param  array   $contact
     * @param  array   $details
     * [
     *  '3ds'=>boolean,
     *  'currency'=>string,
     *  'amount'=>float,
     *  'order_id'=>string,
     *  'order_number'=>string,
     *  'product_id'=>stirng,
     *  'user_agent'=>string,
     *  'descriptor'=>string
     * ]
     * @return array
     */
    private function authorize(array $card, array $contact, array $details): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = [
            'is_flagged'        => false,
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::MINTE,
            'payment_api_id'    => null,
            'hash'              => "fail_" . UtilsService::randomString(16),
            'provider_data'     => null,
            'errors'            => null,
            'token'             => null
        ];

        $api = $this->getPaymentApi($details);
        if (empty($api)) {
            logger()->error("Mint-e PaymentApi not found", ['product_id' => $details['product_id']]);
            return $result;
        }

        $result['payment_api_id'] = (string)$api->getIdAttribute();

        try {
            $route_path = 'authorize';

            // setup 3DS
            $obj3ds = [];
            if ($details['3ds']) {
                $route_path = 'authorize3ds';
                $obj3ds = [
                    'redirecturl'   => 'https://' . request()->getHttpHost() . "/minte-3ds/{$details['order_id']}",
                    'useragent'     => $details['user_agent']
                ];
            }

            $nonce = UtilsService::millitime();
            $body = array_merge(
                [
                    'mid'       => $api->login,
                    'name'      => $contact['first_name'] . ' ' . $contact['last_name'],
                    'address'   => $contact['street'],
                    'city'      => $contact['city'],
                    'zip'       => $contact['zip'],
                    'country'   => $contact['country'],
                    'state'     => $contact['state'],
                    'email'     => $contact['email'],
                    'phone'     => $contact['phone'],
                    'amount'    => $details['amount'],
                    'currency'  => $details['currency'],
                    'orderid'   => $details['order_number'],
                    'nonce'     => $nonce,
                    'signature' => hash('sha256', $api->login . $nonce . $api->key),
                    'cvv'       => $card['cvv'],
                    'expiry'    => $card['month'] . substr($card['year'], 2),
                    'customerip' => $contact['ip'],
                    'cardnumber' => $card['number'],
                    'descriptor' => $details['descriptor']
                ],
                $obj3ds
            );

            $res = $client->put($route_path, [
                'json' => $body
            ]);

            $body_decoded = json_decode($res->getBody(), true);

            if ($body_decoded['status'] === self::STATUS_OK) {
                $result['hash']     = $body_decoded['transid'];
                $result['status']   = Txn::STATUS_CAPTURED;
                $result['token']   = self::encrypt(json_encode($card), $details['order_id']);
            } elseif ($body_decoded['status'] === self::STATUS_3DS) {
                $result['hash']     = $body_decoded['transid'];
                $result['status']   = Txn::STATUS_AUTHORIZED;
                $result['token']   = self::encrypt(json_encode($card), $details['order_id']);
                $result['redirect_url'] = $body_decoded['redirecturl'];
            } else {
                $code = !empty($body_decoded['errorcode']) ? $body_decoded['errorcode'] : $body_decoded['errormessage'];
                if (in_array($code, self::$fallback_codes)) {
                    $result['fallback'] = true;
                }
                $result['errors'] = [MinteCodeMapper::toPhrase($body_decoded['errorcode'], $body_decoded['errormessage'])];
                logger()->error("Mint-e auth", ['body' => $body_decoded]);
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $result['errors'] = [MinteCodeMapper::toPhrase()];

            logger()->error("Mint-e auth", ['response'  => $result['provider_data']]);
        }
        return $result;
    }

    /**
     * Captures payment
     * @param  array   $payment ['hash'=>string,'payment_api_id'=>string]
     * @return array
     */
    private function capture(array $payment): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        $api = $this->getPaymentApi($payment);
        if (empty($api)) {
            logger()->error("Mint-e PaymentApi not found", ['payment_api_id' => $payment['payment_api_id']]);
            return $payment;
        }

        try {
            $nonce = UtilsService::millitime();
            $body = [
                'mid'       => $api->login,
                'nonce'     => $nonce,
                'signature' => hash('sha256', $api->login . $nonce . $api->key),
                'referenceid' => $payment['hash']
            ];

            // logger()->info('Mint-e capture body debug', ['body' => $body]);

            $res = $client->put('capture', [
                'json' => $body
            ]);

            // logger()->info('Mint-e capture res debug', ['body' => $res->getBody()]);

            $body_decoded = json_decode($res->getBody(), true);

            if ($body_decoded['status'] === self::STATUS_OK) {
                $payment['status']   = Txn::STATUS_APPROVED;
            } else {
                $payment['status']   = Txn::STATUS_FAILED;
                $payment['errors'] = [MinteCodeMapper::toPhrase($body_decoded['errorcode'], $body_decoded['errormessage'])];
                logger()->error("Mint-e capture", ['body' => $body_decoded]);
            }
            $payment['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $payment['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $payment['errors'] = [MinteCodeMapper::toPhrase()];
            $payment['status']   = Txn::STATUS_FAILED;

            logger()->error("Mint-e capture", [
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $payment['provider_data']
            ]);
        }
        return $payment;
    }

    /**
     * Provides recurring payment
     * @param  string   $token
     * @param  array    $details ['currency'=>string,'amount'=>float,'descriptor'=>string,'payment_api_id'=>string]
     * @return array
     */
    private function recurring(string $token, array $details): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = [
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::MINTE,
            'hash'              => "fail_" . UtilsService::randomString(16),
            'provider_data'     => null,
            'errors'            => null
        ];

        $api = $this->getPaymentApi($details);
        if (empty($api)) {
            logger()->error("Mint-e PaymentApi not found", ['payment_api_id' => $details['payment_api_id']]);
            return $result;
        }

        $nonce = UtilsService::millitime();
        try {
            $res = $client->put('sale/recurring', [
                'json' => [
                    'mid'       => $api->login,
                    'amount'    => $details['amount'],
                    'currency'  => $details['currency'],
                    'token'     => $token,
                    'nonce'     => $nonce,
                    'signature' => hash('sha256', $api->login . $nonce . $api->key),
                    'descriptor' => $details['descriptor']
                ]
            ]);

            $body_decoded = json_decode($res->getBody(), true);

            logger()->info('Mint-e res debug', ['body' => $res->getBody()]);

            if ($body_decoded['status'] === self::STATUS_OK) {
                $result['hash'] = $body_decoded['midtransid'];
                $result['status'] = Txn::STATUS_APPROVED;
            } else {
                $result['errors'] = [MinteCodeMapper::toPhrase($body_decoded['errorcode'], $body_decoded['errormessage'])];
                logger()->error("Mint-e pay", ['body' => $body_decoded]);
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $result['errors'] = [MinteCodeMapper::toPhrase()];

            logger()->error("Mint-e pay", [
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $result['provider_data']
            ]);
        }
        return $result;
    }

    /**
     * Captures payment after 3ds authorization
     * @param array $params
     * [
     *   'errcode'   => ?string,
     *   'errmsg'    => ?string,
     *   'sign'      => string,
     *   'hash'      => string,
     *   'status'    => string,
     *   'timestamp' => string,
     *   'order_id'  => string,
     *   'payment_api_id' => string
     * ]
     * @return array
     */
    public function captureMinte3ds(array $params): array
    {
        $result = ['status' => false];

        $api = $this->getPaymentApi($params);
        if (empty($api)) {
            logger()->error("PaymentApi not found", ['params' => $params]);
            return $result;
        }

        if ($params['sign'] === hash('sha256', $params['hash'] . $params['timestamp'] . $api->key)) {
            $result = ['status' => true, 'txn' => ['hash' => $params['hash']]];
            if ($params['status'] === self::STATUS_OK) {
                $result['txn'] = $this->capture($params);
            } else {
                $code = !empty($params['errcode']) ? $params['errcode'] : $params['errmsg'];
                if (in_array($code, self::$fallback_codes)) {
                    $result['txn']['fallback'] = true;
                }
                $result['txn']['status'] = Txn::STATUS_FAILED;
                $result['txn']['errors'] = [MinteCodeMapper::toPhrase($params['errcode'], $params['errmsg'])];
            }
        }

        return $result;
    }

}
