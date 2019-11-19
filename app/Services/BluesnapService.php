<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Txn;
use App\Constants\PaymentMethods;
use App\Constants\PaymentProviders;
use App\Mappers\BluesnapCodeMapper;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;

/**
 * BluesnapService class
 */
class BluesnapService
{
    const ENV_LIVE      = 'live';
    const ENV_SANDBOX   = 'sandbox';

    const HTTP_CODE_SUCCESS = 200;
    const HTTP_CODE_ERROR   = 400;

    const TYPE_WEBHOOK_CHARGE   = 'CHARGE';
    const TYPE_WEBHOOK_DECLINE  = 'DECLINE';

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var array
     */
    private $ips;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $data_protection_key;

    /**
     * BluesnapService constructor
     */
    public function __construct()
    {
        $login       = Setting::getValue('bluesnap_login');
        $password    = Setting::getValue('bluesnap_password');
        $protect_key = Setting::getValue('bluesnap_data_protection_key');
        $environment = Setting::getValue('bluesnap_environment', self::ENV_LIVE);
        $ips_str     = Setting::getValue('bluesnap_ips', '');

        if (!$login || !$password || !$protect_key) {
            logger()->error("Bluesnap configuration needs to check");
        }

        $this->ips = \explode(',', $ips_str);
        $this->login = $login;
        $this->password = $password;
        $this->data_protection_key = $protect_key;
        $this->endpoint = 'https://' . ($environment === self::ENV_LIVE ? 'ws' : 'sandbox') . '.bluesnap.com/services/2/';
    }

    /**
     * Returns Card object
     * @param  array $card
     * @param  array $contact
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
            'address'   => $contact['street'],
            'city'      => $contact['city'],
            'country'   => $contact['country'],
            'email'     => $contact['email'],
            'firstName' => $contact['first_name'],
            'lastName'  => $contact['last_name'],
            'phone'     => $phone,
            'state'     => $contact['state'],
            'zip'       => $contact['zip']
        ];
        if (!empty($contact['district'])) {
            $result['address2'] = $contact['district'];
        }
        if (!empty($contact['document_number'])) {
            $result['personalIdentificationNumber'] = $contact['district'];
        }
        return $result;
    }

    /**
     * Provides payment by card
     * @param  array   $card
     * @param  array   $contact
     * @param  array   $order_details ['currency'=>string,'amount'=>float,'billing_descriptor'=>string]
     * @return array
     */
    public function payByCard(array $card, array $contact, array $order_details): array
    {
        return $this->pay(
            [
                'cardHolderInfo'    => self::createCardHolderObj($contact),
                'creditCard'        => self::createCardObj($card, $contact),
            ],
            $order_details
        );
    }

    /**
     * Provides payment by vaulted shopper id
     * @param  array   $shooper_id
     * @param  array   $order_details ['currency'=>string,'amount'=>float,'billing_descriptor'=>string]
     * @return array
     */
    public function payByVaultedShopperId(string $shopper_id, array $order_details): array
    {
        return $this->pay(['vaultedShopperId' => $shopper_id], $order_details);
    }

    /**
     * Provides payment
     * @param  array   $sorce
     * @param  array   $order_details
     * [
     *  'currency'=>string,
     *  'amount'=>float,
     *  'billing_descriptor'=>string,
     * ]
     * @return array
     */
    private function pay(array $source, array $order_details): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'auth' => [$this->login, $this->password],
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = [
            'fee'               => 0,
            'is_flagged'        => false,
            'currency'          => $order_details['currency'],
            'value'             => $order_details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::BLUESNAP,
            'payment_method'    => PaymentMethods::CREDITCARD,
            'hash'              => null,
            'payer_id'          => null,
            'provider_data'     => null,
            'errors'            => null
        ];

        try {
            $res = $client->post('transactions', [
                'json' => \array_merge(
                    [
                        'amount'        => $order_details['amount'],
                        'currency'      => $order_details['currency'],
                        'cardTransactionType'   => 'AUTH_CAPTURE',
                        'softDescriptor'    =>  $order_details['billing_descriptor'],
                        'storeCard'     => true
                    ],
                    $sorce
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
            if ($ex->getCode() === self::HTTP_CODE_ERROR && $res) {
                $body_decoded = \json_decode($res->getBody(), true);
                if (!empty($body_decoded['message'])) {
                    $result['errors'] = array_map(function($v) {
                        return BluesnapCodeMapper::toPhrase($v['errorName']);
                    }, $body_decoded['message']);
                }
            }
            logger()->error("Bluesnap pay", [
                'code'      => $ex->getCode(),
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $result['provider_data']['message']
            ]);
        }
        return $result;
    }

    /**
     * Validates webhook
     * @param  Request $req
     * @return array
     */
    public function validateWebhook(Request $req)
    {
        $authKey    = $req->input('authKey', '');
        $currency   = $req->input('invoiceChargeCurrency');
        $fee        = $req->input('bluesnapManualFee', 0);
        $hash       = $req->input('referenceNumber');
        $type       = $req->input('transactionType');
        $value      = $req->input('invoiceChargeAmount', 0);

        $result = ['status' => false];
        if (in_array($req->ip(), $this->ips)) {
            $result = [
                'status' => true,
                'txn' => [
                    'currency'  => $currency,
                    'fee'       => $fee,
                    'hash'      => $hash,
                    'status'    => $type === self::TYPE_WEBHOOK_CHARGE ? Txn::STATUS_APPROVED : Txn::STATUS_FAILED,
                    'value'     => $value
                ],
                'result' => md5($authKey . 'ok' . $this->data_protection_key)
            ];
        }

        return $result;
    }
}
