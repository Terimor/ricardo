<?php

namespace App\Services;

use App\Models\PaymentApi;
use App\Models\Setting;
use App\Models\Txn;
use App\Constants\PaymentProviders;
use App\Mappers\BluesnapCodeMapper;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;

/**
 * BluesnapService class
 */
class BluesnapService
{
    use ProviderServiceTrait;

    const ENV_LIVE      = 'live';
    const ENV_SANDBOX   = 'sandbox';

    const HTTP_CODE_SUCCESS   = 200;
    const HTTP_REFUND_SUCCESS = 204;
    const HTTP_CODE_ERROR     = 400;

    const TYPE_WEBHOOK_CHARGE   = 'CHARGE';
    const TYPE_WEBHOOK_DECLINE  = 'DECLINE';

    /**
     * @var array
     */
    private static array $fallback_codes = [
        'AUTHORIZATION_EXPIRED',
        'DO_NOT_HONOR',
        'INVALID_TRANSACTION',
        'NO_AVAILABLE_PROCESSORS',
        'PAYMENT_GENERAL_FAILURE',
        'THREE_D_SECURE_FAILURE',
        'VALIDATION_GENERAL_FAILURE',
        'GENERAL_PAYMENT_PROCESSING_ERROR'
    ];

    /**
     * @var string
     */
    private string $endpoint;

    /**
     * BluesnapService constructor
     * @param PaymentApi $api
     */
    public function __construct(PaymentApi $api)
    {
        $environment = Setting::getValue('bluesnap_environment', self::ENV_LIVE);
        $this->api = $api;
        $this->endpoint = 'https://' . ($environment === self::ENV_LIVE ? 'ws' : 'sandbox') . '.bluesnap.com/services/2/';
    }

    /**
     * Returns Card object
     * @param  array $card
     * @return array
     */
    public static function createCardObj(array $card): array
    {
        return [
            'cardNumber'        => $card['number'],
            'expirationMonth'   => $card['month'],
            'expirationYear'    => $card['year'],
            'securityCode'      => $card['cvv']
        ];
    }

    /**
     * Returns CardHolder object
     * @param  array $contacts
     * @return array
     */
    public static function createCardHolderObj(array $contacts): array
    {
        return array_filter([
            'address'   => $contacts['street'] . (!empty($contacts['building']) ? ", {$contacts['building']}" : ''),
            'address2'  => $contacts['complement'] ?? null,
            'city'      => $contacts['city'],
            'country'   => $contacts['country'],
            'email'     => $contacts['email'],
            'firstName' => $contacts['first_name'],
            'lastName'  => $contacts['last_name'],
            'phone'     => is_array($contacts['phone']) ? implode('', $contacts['phone']) : $contacts['phone'],
            'zip'       => $contacts['zip'],
            'state'     => $contacts['state'] ?? null,
            'personalIdentificationNumber' => $contacts['document_number'] ?? null
        ]);
    }

    /**
     * Creates payment response template
     * @param  array $details ['currency'=>string,'amount'=>float,'order_id'=>string]
     * @return array
     */
    public function createPaymentTmpl(array $details): array
    {
        return [
            'is_flagged'        => false,
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_NEW,
            'payment_provider'  => PaymentProviders::BLUESNAP,
            'hash'              => 'new_' . hrtime(true),
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'payer_id'          => null,
            'provider_data'     => null,
            'errors'            => null
        ];
    }

    /**
     * Returns data protection key
     * @return string
     */
    public function getDataProtectionKey(): string
    {
        return $this->api->key;
    }

    /**
     * Provides payment by card
     * @param  array   $card
     * @param  array   $contacts
     * @param  array   $details
     * [
     *   '3ds'=>boolean,
     *   'currency'=>string,
     *   'amount'=>float,
     *   'billing_descriptor'=>string,
     *   'kount_session_id'=>?string,
     *   'order_id'=>string
     * ]
     * @return array
     */
    public function payByCard(array $card, array $contacts, array $details): array
    {
        $payment = [];
        if ($details['3ds']) {
            $payment = $this->createVaultedShopperId($card, $contacts, $details);
            if (!empty($payment['payer_id'])) {
                $pf_token = $this->getPfToken($card);
                $payment['bs_pf_token'] = $pf_token;
                $payment['status'] = !$pf_token ? Txn::STATUS_FAILED : Txn::STATUS_NEW;
            }
        } else {
            $payment = $this->pay(
                [
                    'cardHolderInfo' => self::createCardHolderObj($contacts),
                    'creditCard'     => self::createCardObj($card),
                ],
                $details
            );
        }

        return array_merge(
            $this->createPaymentTmpl($details),
            $payment,
            ['token' => self::encrypt(json_encode($card), $details['order_id'])]
        );
    }

    /**
     * Provides payment by vaulted shopper id
     * @param string $shopper_id
     * @param array $details ['3ds_ref'=>?string, 'currency'=>string, 'amount'=>float, 'billing_descriptor'=>string]
     * @return array
     */
    public function payByVaultedShopperId(string $shopper_id, array $details): array
    {
        return array_merge(
            $this->createPaymentTmpl($details),
            $this->pay(['vaultedShopperId' => $shopper_id], $details)
        );
    }

    /**
     * Provides payment
     * @param  array   $source
     * @param  array   $details ['3ds_ref'=>?string, 'currency'=>string, 'amount'=>float, 'billing_descriptor'=>string]
     * @return array
     */
    private function pay(array $source, array $details): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'auth' => [$this->api->login, $this->api->password],
            'headers' => ['Accept' => 'application/json']
        ]);

        $result = [];
        try {
            $three_d_secure = [];
            if (!empty($details['3ds_ref'])) {
                $three_d_secure['threeDSecure'] = ['threeDSecureReferenceId' => $details['3ds_ref']];
            }

            $res = $client->post('transactions', [
                'json' => array_merge(
                    $source,
                    $three_d_secure,
                    [
                        'amount'   => $details['amount'],
                        'currency' => $details['currency'],
                        'cardTransactionType' => 'AUTH_CAPTURE',
                        'softDescriptor' =>  $details['billing_descriptor'],
                        'storeCard' => true
                    ]
                )
            ]);

            if ($res->getStatusCode() === self::HTTP_CODE_SUCCESS) {
                $body_decoded = \json_decode($res->getBody(), true);
                $result['payer_id'] = $body_decoded['vaultedShopperId'];
                $result['hash']     = $body_decoded['transactionId'];
                $result['currency'] = $body_decoded['currency'];
                $result['value']    = $body_decoded['amount'];
                $result['status']   = Txn::STATUS_CAPTURED;
            } else {
                $result['status']   = Txn::STATUS_FAILED;
                $result['errors'] = [BluesnapCodeMapper::toPhrase()];
                logger()->warning('Bluesnap pay', ['res' => $res]);
            }
            $result['provider_data'] = (string)$res->getBody();
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse() : null;
            $code = optional($res)->getStatusCode();
            $body = optional($res)->getBody();
            $result['provider_data'] = ['code'  => $code, 'body'  => (string)$body];
            $result['status'] = Txn::STATUS_FAILED;
            $result = array_merge($result, $this->parseErrorResponse($body));
            logger()->warning("Bluesnap pay", $result['provider_data']);
        }
        return $result;
    }

    /**
     * Creates vaulted shopper and returns its ID
     * @param  array  $card
     * @param  array  $contacts
     * @param  array  $details ['kount_session_id'=>?string, 'currency'=>string]
     * @return array
     */
    private function createVaultedShopperId(array $card, array $contacts, array $details): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'auth' => [$this->api->login, $this->api->password],
            'headers' => ['Accept' => 'application/json']
        ]);

        $result = ['payer_id' => null, 'status' => Txn::STATUS_FAILED];
        try {
            $fraud_info = [];
            if (!empty($details['kount_session_id'])) {
                $fraud_info = [
                    'transactionFraudInfo' => [
                        'fraudSessionId'   => $details['kount_session_id'],
                        'shopperIpAddress' => $contacts['ip'],
                        'shippingContactInfo' => [
                            'address'   => $contacts['street'] . (!empty($contact['building']) ? ", {$contact['building']}" : ''),
                            'address2'  => $contacts['complement'] ?? null,
                            'city'      => $contacts['city'],
                            'country'   => strtoupper($contacts['country']),
                            'firstName' => $contacts['first_name'],
                            'lastName'  => $contacts['last_name'],
                            'zip'       => $contacts['zip'],
                            'state'     => $contacts['state'] ?? null
                        ]
                    ]
                ];
            }

            $res = $client->post('vaulted-shoppers', [
                'json' => array_merge(
                    $fraud_info,
                    self::createCardHolderObj($contacts),
                    [
                        'shopperCurrency' => $details['currency'],
                        'paymentSources'  => [
                            'creditCardInfo' => [
                                [
                                    'creditCard' => self::createCardObj($card),
                                    'billingContactInfo' => self::createCardHolderObj($contacts)
                                ]
                            ]
                        ]
                    ]
                )
            ]);

            $body_decoded = json_decode($res->getBody(), true);
            $payer_id = $body_decoded['vaultedShopperId'] ?? null;
            $result = ['payer_id' => $payer_id, 'status' => !$payer_id ? Txn::STATUS_FAILED : Txn::STATUS_NEW];
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse() : null;
            $code = optional($res)->getStatusCode();
            $body = optional($res)->getBody();
            logger()->warning("Bluesnap vaulted shopper", ['code'  => $code, 'body'  => $body]);
            $result = array_merge($result, $this->parseErrorResponse($body));
        }
        return $result;
    }

    /**
     * Returns pfToken for a new card payment
     * @param array $card
     * @return string|null
     */
    private function getPfToken(array $card): ?string
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'auth' => [$this->api->login, $this->api->password],
            'headers' => ['Accept' => 'application/json']
        ]);

        $result = null;
        try {
            $res = $client->post('payment-fields-tokens/prefill', [
                'json' => [
                    'ccNumber' => $card['number'],
                    'expDate'  => "{$card['month']}/{$card['year']}"
                ]
            ]);
            $headers = $res->getHeader('Location');
            if (!empty($headers)) {
                $exploded = explode('/', end($headers));
                $result = end($exploded);
            }
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse() : null;
            logger()->warning("Bluesnap pfToken", [
                'code'  => optional($res)->getStatusCode(),
                'body'  => optional($res)->getBody()
            ]);
        }
        return $result;
    }

    /**
     * Parses error response
     * @param string|null $data
     * @return array
     */
    private function parseErrorResponse(?string $data): array
    {
        $result = [];
        $body_decoded = json_decode($data, true);
        if (!empty($body_decoded['message'])) {
            $result = array_reduce($body_decoded['message'], function($carry, $item) {
                $phrase = BluesnapCodeMapper::getPhrase($item['errorName']);
                $carry['fallback'] = in_array($item['errorName'], self::$fallback_codes);
                if (!$phrase && !empty($item['invalidProperty'])) {
                    $code = is_array($item['invalidProperty']) ? $item['invalidProperty']['name'] : $item['invalidProperty'];
                    $phrase = BluesnapCodeMapper::toPhrase($code);
                }
                $carry['errors'][] = $phrase ?? BluesnapCodeMapper::toPhrase();
                return $carry;
            }, ['errors' => [], 'fallback' => false]);
        }
        return $result;
    }

    /**
     * Refunds payment
     * @param  string $hash
     * @param  float|null $amount null - full refund
     * @return array
     */
    public function refund(string $hash, ?float $amount = null): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'auth' => [$this->api->login, $this->api->password],
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = ['status' => false, 'errors' => ["Something went wrong [{$hash}]"]];
        try {
            $path = "transactions/{$hash}/refund";
            if ($amount) {
                $path .= '?amount=' . (string)$amount;
            }

            $res = $client->put($path);

            if ($res->getStatusCode() === self::HTTP_REFUND_SUCCESS) {
                $result['status'] = true;
                unset($result['errors']);
            } else {
                logger()->warning("Bluesnap refund", ['res' => $res]);
            }
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse() : null;

            logger()->warning("Bluesnap refund", ['res'  => $res]);

            if ($ex->getCode() === self::HTTP_CODE_ERROR && $res) {
                $body_decoded = json_decode($res->getBody(), true);
                if (!empty($body_decoded['message'])) {
                    $result['errors'] = array_map(function($v) use ($hash) {
                        return ($v['description'] ?? "Something went wrong") . " [{$hash}]";
                    }, $body_decoded['message']);
                }
            }
        }
        return $result;
    }

    /**
     * Validates webhook
     * @param  Request $req
     * @return array
     */
    public static function validateWebhook(Request $req)
    {
        $currency   = $req->input('invoiceChargeCurrency');
        $hash       = $req->input('referenceNumber');
        $type       = $req->input('transactionType');
        $value      = $req->input('invoiceChargeAmount', 0);

        $result = ['status' => false];

        $ips = explode(',', Setting::getValue('bluesnap_ips', ''));

        if (in_array($req->ip(), $ips)) {
            $result = [
                'status' => true,
                'txn' => [
                    'currency'  => $currency,
                    'hash'      => $hash,
                    'status'    => $type === self::TYPE_WEBHOOK_CHARGE ? Txn::STATUS_APPROVED : Txn::STATUS_FAILED,
                    'value'     => preg_replace('/[^\d.]/', '', $value)
                ]
            ];
        }

        return $result;
    }
}
