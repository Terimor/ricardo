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
        'ae' => ['AED', 'USD'],
        'ar' => ['ARS', 'USD'],
        'au' => ['AUD', 'USD'],
        'at' => ['EUR', 'USD'],
        'be' => ['EUR', 'USD'],
        'bg' => ['BGN', 'USD'],
        'bt' => ['INR', 'USD'],
        'ca' => ['CAD', 'USD'],
        'cc' => ['AUD', 'USD'],
        'ch' => ['CHF', 'USD'],
        'ck' => ['NZD', 'USD'],
        'cl' => ['CLP', 'USD'],
        'co' => ['COP', 'USD'],
        'cx' => ['AUD', 'USD'],
        'cy' => ['EUR', 'USD'],
        'cz' => ['CZK', 'USD'],
        'de' => ['EUR', 'USD'],
        'dk' => ['DKK', 'USD'],
        'ee' => ['EUR', 'USD'],
        'es' => ['EUR', 'USD'],
        'fi' => ['EUR', 'USD'],
        'fr' => ['EUR', 'USD'],
        'gb' => ['GBP', 'USD'],
        'gp' => ['EUR', 'USD'],
        'gr' => ['EUR', 'USD'],
        'hk' => ['HKD', 'USD'],
        'hm' => ['AUD', 'USD'],
        'hr' => ['HRK', 'USD'],
        'hu' => ['HUF', 'USD'],
        'ie' => ['EUR', 'USD'],
        'il' => ['ILS', 'USD'],
        'in' => ['INR', 'USD'],
        'it' => ['EUR', 'USD'],
        'jp' => ['JPY', 'USD'],
        'ki' => ['AUD', 'USD'],
        'kr' => ['KRW', 'USD'],
        'li' => ['CHF', 'USD'],
        'ls' => ['ZAR', 'USD'],
        'lt' => ['EUR', 'USD'],
        'lu' => ['EUR', 'USD'],
        'lv' => ['EUR', 'USD'],
        'mc' => ['EUR', 'USD'],
        'mt' => ['EUR', 'USD'],
        'mx' => ['MXN', 'USD'],
        'my' => ['MYR', 'USD'],
        'na' => ['ZAR', 'USD'],
        'nf' => ['AUD', 'USD'],
        'nl' => ['EUR', 'USD'],
        'no' => ['NOK', 'USD'],
        'nr' => ['AUD', 'USD'],
        'nu' => ['NZD', 'USD'],
        'nz' => ['NZD', 'USD'],
        'ph' => ['PHP', 'USD'],
        'pl' => ['PLN', 'USD'],
        'pn' => ['NZD', 'USD'],
        'pt' => ['EUR', 'USD'],
        'ro' => ['RON', 'USD'],
        'ru' => ['RUB', 'USD'],
        'se' => ['SEK', 'USD'],
        'sg' => ['SGD', 'USD'],
        'si' => ['EUR', 'USD'],
        'sk' => ['EUR', 'USD'],
        'sm' => ['EUR', 'USD'],
        'tk' => ['NZD', 'USD'],
        'tr' => ['TRY', 'USD'],
        'tv' => ['AUD', 'USD'],
        'tw' => ['TWD', 'USD'],
        'us' => ['USD'],
        'va' => ['EUR', 'USD'],
        'za' => ['ZAR', 'USD'],
        '*'  => ['USD']
    ];

    /**
     * @var array
     */
    private static $fallback_codes = ['05', '621', '622', '625', 'Amount by terminal exceeded', 'Company limits exceeded.', 'ERR-MPI'];

    /**
     * @var string
     */
    private $endpoint;


    /**
     * MinteService constructor
     * @param PaymentApi $api
     */
    public function __construct(PaymentApi $api)
    {
        $this->api = $api;
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
     *  'user_agent'=>string,
     *  'descriptor'=>string
     * ]
     * @return array
     */
    public function payByCard(array $card, array $contact, array $details): array
    {
        $phone = $contact['phone'];
        if (gettype($contact['phone']) === 'array') {
            $phone = $contact['phone']['country_code'] . $contact['phone']['number'];
        }
        return $this->authorize($card, array_merge($contact, ['phone' => $phone]), $details);
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
     * ]
     * @return array
     */
    public function payByToken(string $token, array $contact, array $details): array
    {
        return $this->authorize(
            json_decode(self::decrypt($token, $details['order_id']), true),
            $contact,
            array_merge($details, ['3ds' => false])
        );
    }

    /**
     * Refunds payment
     * @param  string $hash
     * @param  float  $amount
     * @return array
     */
    public function refund(string $hash, float $amount): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = ['status' => false];
        try {
            $nonce = UtilsService::millitime();
            $body = [
                'mid'       => $this->api->login,
                'nonce'     => $nonce,
                'amount'    => $amount,
                'signature' => hash('sha256', $this->api->login . $nonce . $this->api->key),
                'referenceid' => $hash
            ];

            $res = $client->put('refund', ['json' => $body]);

            $body_decoded = json_decode($res->getBody(), true);

            if ($body_decoded['status'] === self::STATUS_OK) {
                $result['status'] = true;
            } else {
                logger()->warning("Mint-e refund", ['body' => $body_decoded]);
                $result['errors'] = [($body_decoded['errormessage'] ?? 'Something went wrong') . " [{$hash}]"];
            }
        } catch (GuzzReqException $ex) {
            logger()->warning("Mint-e capture", ['res' => $ex->hasResponse() ? $ex->getResponse()->getBody() : null]);

            $result['errors'] = [($ex->getMessage() ?? 'Something went wrong') . " [{$hash}]"];
        }
        return $result;
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
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'hash'              => "fail_" . UtilsService::randomString(16),
            'provider_data'     => null,
            'errors'            => null,
            'token'             => null
        ];

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
                    'mid'       => $this->api->login,
                    'name'      => $contact['first_name'] . ' ' . $contact['last_name'],
                    'address'   => $contact['street'],
                    'city'      => $contact['city'],
                    'zip'       => $contact['zip'],
                    'country'   => $contact['country'],
                    'state'     => $contact['state'] ?? '',
                    'email'     => $contact['email'],
                    'phone'     => $contact['phone'],
                    'amount'    => $details['amount'],
                    'currency'  => $details['currency'],
                    'orderid'   => $details['order_number'],
                    'nonce'     => $nonce,
                    'signature' => hash('sha256', $this->api->login . $nonce . $this->api->key),
                    'cvv'       => $card['cvv'],
                    'expiry'    => $card['month'] . substr($card['year'], 2),
                    'customerip' => $contact['ip'],
                    'cardnumber' => $card['number'],
                    'descriptor' => $details['descriptor']
                ],
                $obj3ds
            );

            $res = $client->put($route_path, ['json' => $body]);

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
                logger()->warning("Mint-e auth", ['body' => $body_decoded]);

                $code = $body_decoded['errorcode'] ?? null;
                $msg  = $body_decoded['errormessage'] ?? null;

                $result['fallback'] = in_array($code ?? $msg, self::$fallback_codes);
                $result['errors'] = [MinteCodeMapper::toPhrase($code, $msg)];
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $result['errors'] = [MinteCodeMapper::toPhrase()];

            logger()->warning("Mint-e auth", $result['provider_data']);
        }
        return $result;
    }

    /**
     * Captures payment
     * @param  string  $hash
     * @param  array   $details ['currency'=>string,'amount'=>float]
     * @return array
     */
    public function capture(string $hash, array $details): array
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
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'hash'              => "fail_" . UtilsService::randomString(16),
            'provider_data'     => null,
            'errors'            => null,
            'token'             => null
        ];

        try {
            $nonce = UtilsService::millitime();
            $body = [
                'mid'       => $this->api->login,
                'nonce'     => $nonce,
                'signature' => hash('sha256', $this->api->login . $nonce . $this->api->key),
                'referenceid' => $hash
            ];

            $res = $client->put('capture', ['json' => $body]);

            $body_decoded = json_decode($res->getBody(), true);

            if ($body_decoded['status'] === self::STATUS_OK) {
                $result['status'] = Txn::STATUS_APPROVED;
                $result['hash'] = $body_decoded['midtransid'];
            } else {
                logger()->warning("Mint-e capture", ['body' => $body_decoded]);

                $result['status'] = Txn::STATUS_FAILED;
                $result['errors'] = [MinteCodeMapper::toPhrase($body_decoded['errorcode'], $body_decoded['errormessage'])];
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            logger()->warning("Mint-e capture", ['res'  => $res]);

            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $result['errors'] = [MinteCodeMapper::toPhrase()];
            $result['status']   = Txn::STATUS_FAILED;
        }
        return $result;
    }

    /**
     * Provides recurring payment
     * @param  string   $token
     * @param  array    $details ['currency'=>string,'amount'=>float,'descriptor'=>string,]
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

        $nonce = UtilsService::millitime();
        try {
            $res = $client->put('sale/recurring', [
                'json' => [
                    'mid'       => $this->api->login,
                    'amount'    => $details['amount'],
                    'currency'  => $details['currency'],
                    'token'     => $token,
                    'nonce'     => $nonce,
                    'signature' => hash('sha256', $this->api->login . $nonce . $this->api->key),
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
                logger()->warning("Mint-e pay", ['body' => $body_decoded]);
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $result['errors'] = [MinteCodeMapper::toPhrase()];

            logger()->warning("Mint-e pay", ['res'  => $result['provider_data']]);
        }
        return $result;
    }

    /**
     * Handles payment after 3ds
     * @param array $payment Order->txns item
     * @param array $params ['errcode' => ?string, 'errmsg' => ?string, 'status' => string]
     * @return array
     */
    public function handle3ds(array $payment, array $params): array
    {
        if ($params['status'] === self::STATUS_OK) {
            $payment['status'] = Txn::STATUS_CAPTURED;
        } else {
            $code = !empty($params['errcode']) ? $params['errcode'] : $params['errmsg'];
            if (in_array($code, self::$fallback_codes)) {
                $payment['fallback'] = true;
            }
            $payment['status'] = Txn::STATUS_FAILED;
            $payment['errors'] = [MinteCodeMapper::toPhrase($params['errcode'], $params['errmsg'])];
        }

        return $payment;
    }

    /**
     * Verifies signature
     * @param  string $hash
     * @param  string $sign
     * @param  string $ts
     * @return bool
     */
    public function verifySignature(string $hash, string $sign, string $ts): bool
    {
        return $sign === hash('sha256', $hash . $ts . $this->api->key);
    }

}
