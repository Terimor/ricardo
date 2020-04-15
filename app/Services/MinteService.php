<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Setting;
use App\Models\PaymentApi;
use App\Models\Txn;
use App\Constants\PaymentProviders;
use App\Mappers\MinteCodeMapper;
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

    const ST_SUCCESS    = 'SUCCESS';
    const ST_PENDING    = 'PENDING';
    const ST_FAILED     = 'FAILED';

    const COUNTRY_CURRENCY = [
        'ae' => ['AED', 'USD'],
        'ar' => ['ARS', 'USD'],
        'au' => ['AUD', 'USD'],
        'at' => ['EUR'],
        'be' => ['EUR'],
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
        'id' => ['USD'],
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
        'nl' => ['EUR'],
        'no' => ['NOK', 'USD'],
        'nr' => ['AUD', 'USD'],
        'nu' => ['NZD', 'USD'],
        'nz' => ['NZD', 'USD'],
        'ph' => ['PHP', 'USD'],
        'pl' => ['PLN'],
        'pn' => ['NZD', 'USD'],
        'pr' => ['USD'],
        'pt' => ['EUR', 'USD'],
        'ro' => ['RON', 'USD'],
        'ru' => ['RUB', 'USD'],
        'sa' => ['USD'],
        'se' => ['SEK', 'USD'],
        'sg' => ['SGD', 'USD'],
        'si' => ['EUR', 'USD'],
        'sk' => ['EUR', 'USD'],
        'sm' => ['EUR', 'USD'],
        'th' => ['USD'],
        'tk' => ['NZD', 'USD'],
        'tr' => ['TRY', 'USD'],
        'tv' => ['AUD', 'USD'],
        'tw' => ['TWD', 'USD'],
        'us' => ['USD'],
        'uy' => ['USD'],
        'va' => ['EUR', 'USD'],
        'vn' => ['USD'],
        'za' => ['ZAR', 'USD'],
        '*'  => ['USD']
    ];

    /**
     * @var array
     */
    private static array $fallback_codes = ['621', '622', '625', 'ERR-MPI'];

    /**
     * @var array
     */
    private static array $fallback_types = ['2', '3'];

    /**
     * @var array
     */
    private static array $fallback_messages = ['Amount by terminal exceeded', 'Company limits exceeded.'];

    /**
     * @var string
     */
    private string $endpoint;

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
        } elseif (in_array($currency, self::COUNTRY_CURRENCY['*'])) {
            $result = true;
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
            'payment_provider'  => PaymentProviders::MINTE,
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'payer_id'          => null,
            'provider_data'     => null,
            'errors'            => null
        ];
    }

    /**
     * Checks is it fallback
     * @param array $error ['errortype' => string|null, 'errorcode' => string|null, 'errormessage' => string|null]
     * @return bool
     */
    private function checkErrorToFallback(array $error): bool
    {
        if (!empty($error['errortype']) && in_array($error['errortype'], self::$fallback_types)) {
            return true;
        } elseif (!empty($error['errorcode']) && in_array($error['errorcode'], self::$fallback_codes)) {
            return true;
        }
        return in_array($error['errormessage'] ?? null, self::$fallback_messages);
    }

    /**
     * Returns Domain for current PaymentApi
     * @return Domain|null
     */
    public function getDomain(): ?Domain
    {
        $result = null;
        if (!empty($this->api->main_domain_id)) {
            $result = Domain::getById($this->api->main_domain_id);
        }
        return $result;
    }

    /**
     * Provides payment by card
     * @param  array   $card
     * @param  array   $contacts
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
    public function payByCard(array $card, array $contacts, array $details): array
    {
        return $this->authorize(
            $card,
            array_merge(
                $contacts,
                ['phone' => is_array($contacts['phone']) ? implode('', $contacts['phone']) : $contacts['phone']]
            ),
            $details
        );
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
            $nonce = UtilsService::millitime() . mt_rand(0, 999);
            $body = [
                'mid'       => $this->api->login,
                'nonce'     => $nonce,
                'amount'    => $amount,
                'signature' => hash('sha256', $this->api->login . $nonce . $this->api->key),
                'referenceid' => $hash
            ];

            $res = $client->put('refund', ['json' => $body]);

            $body_decoded = json_decode($res->getBody(), true);

            if ($body_decoded['status'] === self::ST_SUCCESS) {
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

        $result = $this->createPaymentTmpl($details);

        try {
            $route_path = 'authorize';

            // setup 3DS
            $obj3ds = [];
            if ($details['3ds']) {
                $route_path = 'authorize3ds';
                $obj3ds = [
                    'redirecturl' => 'https://' . request()->getHttpHost() . "/minte-3ds/{$details['order_id']}",
                    'useragent' => $details['user_agent']
                ];
            }

            $nonce = UtilsService::millitime() . mt_rand(0, 999);
            $body = array_merge(
                [
                    'mid'       => $this->api->login,
                    'name'      => $contact['first_name'] . ' ' . $contact['last_name'],
                    'address'   => $contact['street'],
                    'city'      => $contact['city'],
                    'zip'       => substr($contact['zip'], 0, 15),
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

            if ($body_decoded['status'] === self::ST_SUCCESS) {
                $result['hash']     = $body_decoded['transid'];
                $result['status']   = Txn::STATUS_CAPTURED;
                $result['token']   = self::encrypt(json_encode($card), $details['order_id']);
            } elseif ($body_decoded['status'] === self::ST_PENDING) {
                $result['hash']     = $body_decoded['transid'];
                $result['status']   = Txn::STATUS_AUTHORIZED;
                $result['token']   = self::encrypt(json_encode($card), $details['order_id']);
                $result['redirect_url'] = $body_decoded['redirecturl'];
            } else {
                logger()->warning("Mint-e auth", [
                    'req' => array_merge($body, ['cardnumber' => UtilsService::prepareCardNumber($card['number'])]),
                    'res' => $body_decoded
                ]);

                $result['hash'] = $body_decoded['transid'] ?? ('fail_' . hrtime(true));
                $result['status'] = Txn::STATUS_FAILED;
                $result['fallback'] = $this->checkErrorToFallback($body_decoded);
                $result['errors'] = [MinteCodeMapper::toPhrase($body_decoded['errorcode'] ?? null, $body_decoded['errormessage'] ?? null)];
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['hash'] = 'fail_' . hrtime(true);
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
            'hash'              => 'fail_' . hrtime(true),
            'provider_data'     => null,
            'errors'            => null,
            'token'             => null
        ];

        try {
            $nonce = UtilsService::millitime() . mt_rand(0, 999);
            $body = [
                'mid'       => $this->api->login,
                'nonce'     => $nonce,
                'signature' => hash('sha256', $this->api->login . $nonce . $this->api->key),
                'referenceid' => $hash
            ];

            $res = $client->put('capture', ['json' => $body]);

            $body_decoded = json_decode($res->getBody(), true);

            if ($body_decoded['status'] === self::ST_SUCCESS) {
                $result['status'] = Txn::STATUS_APPROVED;
                $result['hash'] = $body_decoded['midtransid'];
            } else {
                logger()->warning("Mint-e capture", ['req' => $body,'res' => $body_decoded]);

                $result['status'] = Txn::STATUS_FAILED;
                $result['errors'] = [MinteCodeMapper::toPhrase($body_decoded['errorcode'], $body_decoded['errormessage'])];
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $result['errors'] = [MinteCodeMapper::toPhrase()];
            $result['status']   = Txn::STATUS_FAILED;

            logger()->warning("Mint-e capture", $result['provider_data']);
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
            'hash'              => 'fail_' . hrtime(true),
            'provider_data'     => null,
            'errors'            => null
        ];

        $nonce = UtilsService::millitime() . mt_rand(0, 999);
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

            if ($body_decoded['status'] === self::ST_SUCCESS) {
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
     * Processes APM payments
     * @param  string $method
     * @param  array  $contacts
     * @param  array  $details
     * [
     *  'currency'=>string,
     *  'amount'=>float,
     *  'order_id'=>string,
     *  'order_number'=>string,
     *  'order_desc'=>string,
     *  'user_agent'=>string
     * ]
     * @return array
     */
    public function payApm(string $method, array $contacts, array $details): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept' => 'application/json']
        ]);

        $result = $this->createPaymentTmpl($details);

        $domain = $this->getDomain();
        if (!$domain) {
            $result['errors'] = [MinteCodeMapper::toPhrase()];
            $result['provider_data'] = ['message' => "PaymentApi [{$this->api->getAttribute()}]. Domain not found"];
            logger()->warning("Mint-e apm", $result['provider_data']);
            return $result;
        }

        try {
            $nonce = UtilsService::millitime() . mt_rand(0, 999);
            $res = $client->put('apm', [
                'json' => [
                    'mid'       => $this->api->login,
                    'type'      => $method,
                    'nonce'     => $nonce,
                    'name'      => $contacts['first_name'] . ' ' . $contacts['last_name'],
                    'address'   => $contacts['street'],
                    'city'      => $contacts['city'],
                    'zip'       => substr($contacts['zip'], 0, 15),
                    'country'   => $contacts['country'],
                    'state'     => $contacts['state'] ?? '',
                    'email'     => $contacts['email'],
                    'phone'     => is_array($contacts['phone']) ? implode('', $contacts['phone']) : $contacts['phone'],
                    'domain'    => $domain->name,
                    'amount'    => $details['amount'],
                    'currency'  => $details['currency'],
                    'orderid'   => $details['order_number'],
                    'orderdesc' => $details['order_desc'],
                    'signature' => hash('sha256', $this->api->login . $nonce . $this->api->key),
                    'useragent' => $details['user_agent'],
                    'customerip' => $contacts['ip'],
                    'redirecturl' => 'https://' . request()->getHttpHost() . "/minte-apm/{$details['order_id']}"
                ]
            ]);

            $body_decoded = json_decode($res->getBody(), true);

            $result['provider_data'] = ['code' => $res->getStatusCode(), 'body' => (string)$res->getBody()];

            if ($body_decoded['status'] === self::ST_PENDING) {
                $result['hash']   = $body_decoded['transid'];
                $result['status'] = Txn::STATUS_AUTHORIZED;
                $result['redirect_url'] = $body_decoded['redirecturl'];
            } else {
                logger()->warning("Mint-e apm", $result['provider_data']);

                $result['hash'] = 'fail_' . hrtime(true);
                $result['status'] = Txn::STATUS_FAILED;
                $result['errors'] = [
                    MinteCodeMapper::toPhrase($body_decoded['errorcode'] ?? null, $body_decoded['errormessage'] ?? null)
                ];
            }
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['hash'] = 'fail_' . hrtime(true);
            $result['errors'] = [MinteCodeMapper::toPhrase()];
            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];

            logger()->warning("Mint-e apm", $result['provider_data']);
        }
        return $result;
    }

    /**
     * Handles payment after 3ds
     * @param array $payment Order->txns item
     * @param array $params ['errortype' => ?string, 'errorcode' => ?string, 'errormessage' => ?string, 'status' => string]
     * @return array
     */
    public function handle3ds(array $payment, array $params): array
    {
        if ($params['status'] === self::ST_SUCCESS) {
            $payment['status'] = Txn::STATUS_CAPTURED;
        } else {
            $payment['status'] = Txn::STATUS_FAILED;
            $payment['fallback'] = $this->checkErrorToFallback($params);
            $payment['errors'] = [MinteCodeMapper::toPhrase($params['errorcode'] ?? null, $params['errormessage'] ?? null)];
        }

        return $payment;
    }

    /**
     * Handles APM redirect response
     * @param array $payment Order->txns item
     * @param array $params ['errcode' => ?string, 'errmsg' => ?string, 'status' => string]
     * @return array
     */
    public function handleApm(array $payment, array $params): array
    {
        if ($params['status'] === self::ST_SUCCESS) {
            $payment['status'] = Txn::STATUS_APPROVED;
        } else {
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
