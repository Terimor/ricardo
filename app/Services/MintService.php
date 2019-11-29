<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Txn;
use App\Constants\PaymentMethods;
use App\Constants\PaymentProviders;
use App\Mappers\MintCodeMapper;
use Illuminate\Http\Request;
use App\Http\Requests\PaymentCardMinte3dsRequest;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;

/**
 * MintService class
 */
class MintService
{
    const ENV_LIVE      = 'live';
    const ENV_SANDBOX   = 'sandbox';

    const STATUS_OK     = 'SUCCESS';
    const STATUS_3DS    = 'PENDING';
    const STATUS_FAIL   = 'FAILED';

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $mid;

    /**
     * MintService constructor
     */
    public function __construct()
    {
        $mid = Setting::getValue('mint_mid');
        $key = Setting::getValue('mint_api_key');
        $environment = Setting::getValue('mint_environment', self::ENV_LIVE);

        if (!$mid || !$key) {
            logger()->error("Mint configuration needs to check");
        }

        $this->mid = $mid;
        $this->api_key = $key;
        $this->endpoint = 'https://' . ($environment === self::ENV_LIVE ? 'prod' : 'test') . '.mint-e.com/process/v1.0/';
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
        $payment = $this->authorize(
            array_merge($card, ['year' => substr($card['year'], 2)]),
            array_merge(
                $contact,
                [
                    'phone' => $contact['phone']['country_code'] . $contact['phone']['number']
                ]
            ),
            $details
        );
        if ($payment['status'] === Txn::STATUS_CAPTURED) {
            return $this->capture($payment);
        }
        return $payment;
    }

    /**
     * Provides payment by token
     * @param  array   $card
     * @param  array   $details ['currency'=>string,'amount'=>float,'descriptor'=>string]
     * @return array
     */
    public function payByToken(string $token, array $details): array
    {
        return $this->recurring($token, $details);
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
            'payment_provider'  => PaymentProviders::MINT,
            'hash'              => "fail_" . UtilsService::randomString(16),
            'provider_data'     => null,
            'errors'            => null,
            'token'             => null,
        ];

        try {
            $route_path = 'authorize';

            // setup 3DS
            $obj3ds = [];
            if ($details['3ds']) {
                $route_path = 'authorize3ds';
                $obj3ds = [
                    'redirecturl'   => request()->getSchemeAndHttpHost() . "/minte-3ds/{$details['order_id']}",
                    'useragent'     => $details['user_agent']
                ];
            }

            $nonce = UtilsService::millitime();
            $body = array_merge(
                [
                    'mid'       => $this->mid,
                    'name'      => $contact['first_name'] . ' ' . $contact['last_name'],
                    'address'   => $contact['street'],
                    'city'      => $contact['city'],
                    'zip'       => $contact['zip'],
                    'country'   => $contact['country'],
                    'state'     => $contact['state'],
                    'email'     => $contact['email'],
                    'phone'     => $contact['phone'],
                    'customerip' => $contact['ip'],
                    'amount'    => $details['amount'],
                    'currency'  => $details['currency'],
                    'orderid'   => $details['order_number'],
                    'nonce'     => $nonce,
                    // 'recurring' => true,
                    'signature' => hash('sha256', $this->mid . $nonce . $this->api_key),
                    'cvv'       => $card['cvv'],
                    'expiry'    => $card['month'] . $card['year'],
                    'cardnumber' => $card['number'],
                    'descriptor' => $details['descriptor']
                ],
                $obj3ds
            );

            logger()->info('Mint-e req debug', ['body' => $body]);

            $res = $client->put($route_path, [
                'json' => $body
            ]);

            logger()->info('Mint-e res debug', ['body' => $res->getBody()]);

            $body_decoded = json_decode($res->getBody(), true);

            if ($body_decoded['status'] === self::STATUS_OK) {
                $result['hash']     = $body_decoded['transid'];
                $result['token']    = $body_decoded['token'];
                $result['status']   = Txn::STATUS_CAPTURED;
            } elseif ($body_decoded['status'] === self::STATUS_3DS) {
                $result['hash']     = $body_decoded['transid'];
                $result['status']   = Txn::STATUS_AUTHORIZED;
                $result['redirect_url'] = $body_decoded['redirecturl'];
            } else {
                $result['errors'] = [MintCodeMapper::toPhrase($body_decoded['errorcode'])];
                logger()->error("Mint-e auth", ['body' => $body_decoded]);
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $result['errors'] = [MintCodeMapper::toPhrase()];

            logger()->error("Mint-e auth", [
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $result['provider_data']
            ]);
        }
        return $result;
    }

    /**
     * Captures payment
     * @param  array   $payment ['hash'=>string]
     * @return array
     */
    private function capture(array $payment): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        try {
            $nonce = UtilsService::millitime();
            $body = [
                'mid'       => $this->mid,
                'nonce'     => $nonce,
                'signature' => hash('sha256', $this->mid . $nonce . $this->api_key),
                'referenceid' => $payment['hash']
            ];

            logger()->info('Mint-e capture body debug', ['body' => $body]);

            $res = $client->put('capture', [
                'json' => $body
            ]);

            logger()->info('Mint-e capture res debug', ['body' => $res->getBody()]);

            $body_decoded = json_decode($res->getBody(), true);

            if ($body_decoded['status'] === self::STATUS_OK) {
                $payment['hash']     = $body_decoded['midtransid'];
                $payment['status']   = Txn::STATUS_APPROVED;
            } else {
                $payment['status']   = Txn::STATUS_FAILED;
                $payment['errors'] = [MintCodeMapper::toPhrase($body_decoded['errorcode'])];
                logger()->error("Mint-e capture", ['body' => $body_decoded]);
            }
            $payment['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $payment['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $payment['errors'] = [MintCodeMapper::toPhrase()];
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
     * @param  array    $details ['currency'=>string,'amount'=>float,'descriptor'=>string]
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
            'payment_provider'  => PaymentProviders::MINT,
            'hash'              => "fail_" . UtilsService::randomString(16),
            'provider_data'     => null,
            'errors'            => null
        ];

        $nonce = UtilsService::millitime();
        try {
            $res = $client->put('sale/recurring', [
                'json' => [
                    'mid'       => $this->mid,
                    'amount'    => $details['amount'],
                    'currency'  => $details['currency'],
                    'token'     => $token,
                    'nonce'     => $nonce,
                    'signature' => hash('sha256', $this->mid . $nonce . $this->api_key),
                    'descriptor' => $details['descriptor']
                ]
            ]);

            $body_decoded = json_decode($res->getBody(), true);

            logger()->info('Mint-e res debug', ['body' => $res->getBody()]);

            if ($body_decoded['status'] === self::STATUS_OK) {
                $result['hash'] = $body_decoded['midtransid'];
                $result['status'] = Txn::STATUS_APPROVED;
            } else {
                $result['errors'] = [MintCodeMapper::toPhrase($body_decoded['errorcode'])];
                logger()->error("Mint-e pay", ['body' => $body_decoded]);
            }
            $result['provider_data'] = $body_decoded;
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse()->getBody() : null;

            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => (string)$res];
            $result['errors'] = [MintCodeMapper::toPhrase()];

            logger()->error("Mint-e pay", [
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $result['provider_data']
            ]);
        }
        return $result;
    }

    /**
     * Captures payment after 3ds authorization
     * @param PaymentCardMinte3dsRequest $req
     * @return array
     */
    public function captureAfterAuth3ds(PaymentCardMinte3dsRequest $req): array
    {
        $err_code   = $req->input('errorcode');
        $sign       = $req->input('signature');
        $txn_hash   = $req->input('transid');
        $txn_status = $req->input('status');
        $txn_ts     = $req->input('timestamp');

        $result = ['status' => false];

        if ($sign === hash('sha256', $txn_hash . $txn_ts . $this->api_key)) {
            $result = ['status' => true, 'txn' => ['hash' => $txn_hash]];
            if ($txn_status === self::STATUS_OK) {
                $result['txn'] = $this->capture(['hash' => $txn_hash]);
            } else {
                $result['txn']['status'] = Txn::STATUS_FAILED;
                $result['txn']['errors'] = [MintCodeMapper::toPhrase($err_code)];
            }
        }

        return $result;
    }

}
