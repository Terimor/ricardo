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
     * BluesnapService constructor
     */
    public function __construct()
    {
        $keys = PaymentApi::getAllByProvider(PaymentProviders::BLUESNAP);

        if (empty($keys)) {
            logger()->error("Bluesnap configuration needs to check");
        }

        $this->keys = $keys;

        $environment = Setting::getValue('bluesnap_environment', self::ENV_LIVE);
        $ips_str     = Setting::getValue('bluesnap_ips', '');

        $this->ips = \explode(',', $ips_str);
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
     * Returns data protection key
     * @param  array  $data ['product_id'=>?string, 'payment_api_id'=>?string]
     * @return string|null
     */
    public function getDataProtectionKey(array $data): ?string
    {
        $api = $this->getPaymentApi($data);
        return optional($api)->key;
    }

    /**
     * Provides payment by card
     * @param  array   $card
     * @param  array   $contact
     * @param  array   $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'product_id'=>string,
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
     *  'product_id'=>?string,
     *  'payment_api_id'=>?string,
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
            'payment_api_id'    => null,
            'payer_id'          => null,
            'provider_data'     => null,
            'errors'            => null
        ];

        $api = $this->getPaymentApi($details);
        if (empty($api)) {
            logger()->error("Ebanx PaymentApi not found [{$details['number']}]");
            return $result;
        }

        $result['payment_api_id'] = (string)$api->getIdAttribute();

        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'auth' => [$api->login, $api->password],
            'headers' => ['Accept'  => 'application/json']
        ]);

        try {
            $res = $client->post('transactions', [
                'json' => \array_merge(
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
                        return BluesnapCodeMapper::toPhrase($v['errorName']);
                    }, $body_decoded['message']);
                }
                $result['provider_data']['res'] = $body_decoded;
            }
            logger()->error("Bluesnap pay", ['res'  => $result['provider_data']]);
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
        $currency   = $req->input('invoiceChargeCurrency');
        $hash       = $req->input('referenceNumber');
        $type       = $req->input('transactionType');
        $value      = $req->input('invoiceChargeAmount', 0);

        $result = ['status' => false];
        if (in_array($req->ip(), $this->ips)) {
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
