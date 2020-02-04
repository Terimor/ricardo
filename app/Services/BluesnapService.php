<?php

namespace App\Services;

use App\Models\PaymentApi;
use App\Models\Setting;
use App\Models\Txn;
use App\Constants\PaymentMethods;
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
     * @var string
     */
    private $endpoint;

    /**
     * BluesnapService constructor
     * @param PaymentApi $api
     */
    public function __construct(PaymentApi $api)
    {
        $this->api = $api;

        $environment = Setting::getValue('bluesnap_environment', self::ENV_LIVE);
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
     * @param  array $contact
     * @return array
     */
    public static function createCardHolderObj(array $contact): array
    {
        $phone = $contact['phone'];
        if (\gettype($contact['phone']) === 'array') {
            $phone = $contact['phone']['country_code'] . $contact['phone']['number'];
        }
        $result = [
            'address'   => $contact['street'] . (!empty($contact['building']) ? ", {$contact['building']}" : ''),
            'city'      => $contact['city'],
            'country'   => $contact['country'],
            'email'     => $contact['email'],
            'firstName' => $contact['first_name'],
            'lastName'  => $contact['last_name'],
            'phone'     => $phone,
            'zip'       => $contact['zip']
        ];
        if (!empty($contact['state'])) {
            $result['state'] = $contact['state'];
        }

        if (!empty($contact['complement'])) {
            $result['address2'] = $contact['complement'];
        }

        if (!empty($contact['document_number'])) {
            $result['personalIdentificationNumber'] = $contact['document_number'];
        }
        return $result;
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
     * @param  array   $contact
     * @param  array   $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'billing_descriptor'=>string
     * ]
     * @return array
     */
    public function payByCard(array $card, array $contact, array $details): array
    {
        return $this->pay(
            [
                'cardHolderInfo'    => self::createCardHolderObj($contact),
                'creditCard'        => self::createCardObj($card),
            ],
            $details
        );
    }

    /**
     * Provides payment by vaulted shopper id
     * @param  array   $shooper_id
     * @param  array   $details ['currency'=>string,'amount'=>float,'billing_descriptor'=>string]
     * @return array
     */
    public function payByVaultedShopperId(string $shopper_id, array $details): array
    {
        return $this->pay(['vaultedShopperId' => $shopper_id], $details);
    }

    /**
     * Provides payment
     * @param  array   $source
     * @param  array   $details
     * [
     *  'currency'=>string,
     *  'amount'=>float,
     *  'billing_descriptor'=>string,
     * ]
     * @return array
     */
    private function pay(array $source, array $details): array
    {
        $result = [
            'is_flagged'        => false,
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::BLUESNAP,
            'hash'              => "fail_" . UtilsService::randomString(16),
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'payer_id'          => null,
            'provider_data'     => null,
            'errors'            => null
        ];

        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'auth' => [$this->api->login, $this->api->password],
            'headers' => ['Accept'  => 'application/json']
        ]);

        try {
            $res = $client->post('transactions', [
                'json' => array_merge(
                    [
                        'amount'        => $details['amount'],
                        'currency'      => $details['currency'],
                        'cardTransactionType'   => 'AUTH_CAPTURE',
                        'softDescriptor'    =>  $details['billing_descriptor'],
                        'storeCard'     => true
                    ],
                    $source
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
                $result['errors'] = [BluesnapCodeMapper::toPhrase()];
                logger()->error("Bluesnap pay", ['res' => $res]);
            }
            $result['provider_data'] = (string)$res->getBody();
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse() : null;
            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => null];
            if ($ex->getCode() === self::HTTP_CODE_ERROR && $res) {
                $body_decoded = \json_decode($res->getBody(), true);
                if (!empty($body_decoded['message'])) {
                    $result['errors'] = array_map(function($v) {
                        $phrase = BluesnapCodeMapper::getPhrase($v['errorName']);
                        if (!$phrase && !empty($v['invalidProperty'])) {
                            $code = is_array($v['invalidProperty']) ? $v['invalidProperty']['name'] : $v['invalidProperty'];
                            $phrase = BluesnapCodeMapper::toPhrase($code);
                        }
                        return $phrase ?? BluesnapCodeMapper::toPhrase();
                    }, $body_decoded['message']);
                }
                $result['provider_data']['res'] = $body_decoded;
            }
            logger()->error("Bluesnap pay", ['res'  => $result['provider_data']]);
        }
        return $result;
    }

    /**
     * Refunds payment
     * @param  string $id
     * @param  float|null $amount null - full refund
     * @return array
     */
    public function refund(string $id, ?float $amount = null): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'auth' => [$this->api->login, $this->api->password],
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = ['status' => false, 'errors' => ['Something went wrong']];
        try {
            $path = "transactions/{$id}/refund";
            if ($amount) {
                $path .= '?amount=' . (string)$amount;
            }

            $res = $client->put($path);

            if ($res->getStatusCode() === self::HTTP_REFUND_SUCCESS) {
                $result['status'] = true;
                unset($result['errors']);
            } else {
                logger()->error("Bluesnap refund", ['res' => $res]);
            }
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse() : null;

            logger()->error("Bluesnap refund", ['res'  => $res]);

            if ($ex->getCode() === self::HTTP_CODE_ERROR && $res) {
                $body_decoded = json_decode($res->getBody(), true);
                if (!empty($body_decoded['message'])) {
                    $result['errors'] = array_map(function($v) {
                        return $v['description'] ?? 'Something went wrong';
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
