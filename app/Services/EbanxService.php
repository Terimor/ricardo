<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\PaymentApi;
use App\Models\Txn;
use App\Mappers\EbanxCodeMapper;
use App\Constants\PaymentMethods;
use App\Constants\PaymentProviders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Ebanx\Benjamin\Models\Address;
use Ebanx\Benjamin\Models\Card;
use Ebanx\Benjamin\Models\Configs\Config;
use Ebanx\Benjamin\Models\Configs\CreditCardConfig;
use Ebanx\Benjamin\Models\Country;
use Ebanx\Benjamin\Models\Currency as EbanxCurrency;
use Ebanx\Benjamin\Models\Payment;
use Ebanx\Benjamin\Models\Person;
use Ebanx\Benjamin\Models\Item;
use Ebanx\Benjamin\Models\Notification;
use Ebanx\Benjamin\Util\Http as EbanxUtils;
use Ebanx\Benjamin\Services\Http\Client as EbanxClient;

/**
 * Class EbanxService
 * @package App\Services
 */
class EbanxService extends ProviderService
{
    const ENV_LIVE      = 'live';
    const ENV_SANDBOX   = 'sandbox';

    const INSTALLMENTS_MIN = 1;

    const STATUS_OK      = 'SUCCESS';
    const STATUS_ERROR   = 'ERROR';

    const PAYMENT_STATUS_PENDING    = 'PE';
    const PAYMENT_STATUS_CONFIRMED  = 'CO';
    const PAYMENT_STATUS_CANCELLED  = 'CA';

    const CUR_PER_COUNTRY = [
        Country::ARGENTINA  => [EbanxCurrency::ARS, EbanxCurrency::USD],
        Country::BRAZIL     => [EbanxCurrency::BRL, EbanxCurrency::USD, EbanxCurrency::EUR],
        Country::BOLIVIA    => [EbanxCurrency::BOB, EbanxCurrency::USD],
        Country::CHILE      => [EbanxCurrency::CLP, EbanxCurrency::USD, EbanxCurrency::EUR],
        Country::COLOMBIA   => [EbanxCurrency::COP, EbanxCurrency::USD, EbanxCurrency::EUR],
        Country::ECUADOR    => [EbanxCurrency::USD],
        Country::MEXICO     => [EbanxCurrency::MXN, EbanxCurrency::USD],
        Country::PERU       => [EbanxCurrency::PEN, EbanxCurrency::USD]
    ];

    /**
     * @var string
     */
    private $environment = self::ENV_LIVE;

    /**
     * EbanxService constructor
     * @param PaymentApi $api
     */
    public function __construct(PaymentApi $api)
    {
        parent::__construct($api);
        $this->environment = Setting::getValue('ebanx_api_environment', self::ENV_LIVE);
    }

    /**
     * Returns Address Model
     * @param  array $contact
     * @return Address
     */
    public static function createAddress(array $contact): Address
    {
        return new Address([
            'address'   => $contact['street'],
            'city'      => $contact['city'],
            'country'   => Country::fromIso($contact['country']),
            'state'     => $contact['state'] ?? '',
            'zipcode'   => $contact['zip'],
            'streetNumber' => $contact['building'] ?? '',
            'streetComplement' => !empty($contact['complement']) ? substr($contact['complement'], 0, 100) : ''
        ]);
    }

    /**
     * Returns Person Model
     * @param  array $contact
     * @return Person
     */
    public static function createPerson(array $contact): Person
    {
        $phone = $contact['phone'];
        if (\gettype($contact['phone']) === 'array') {
            $phone = $contact['phone']['country_code'] . $contact['phone']['number'];
        }
        return new Person([
            'type'          => Person::TYPE_PERSONAL,
            'document'      => $contact['document_number'] ?? '', // maybe undefined
            'email'         => $contact['email'],
            'ip'            => $contact['ip'] ?? null,
            'name'          => $contact['first_name'] . ' ' . $contact['last_name'],
            'phoneNumber'   => $phone
        ]);
    }

    /**
     * Returns CardSource from CreditCard
     * @param  array      $card
     * @param  array      $contact
     * @return Card
     */
    public static function createCardSource(array $card, array $contact): Card
    {
        return new Card([
            'cvv'           => $card['cvv'],
            'dueDate'       => \DateTime::createFromFormat('n-Y', $card['month'] . '-' . $card['year']),
            'name'          => $contact['first_name'] . ' ' . $contact['last_name'],
            'number'        => $card['number'],
            'type'          => PaymentMethods::CREDITCARD
        ]);
    }

    /**
     * Returns CardSource from token
     * @param   string $token
     * @return  Card
     */
    public static function createTokenSource(string $token): Card
    {
        return new Card(['token' => $token]);
    }

    /**
     * Returns Item
     * @param array $data
     * @return Item Item
     */
    public static function createItem(array $data): Item
    {
        return new Item([
            'sku'       => $data['sku'],
            'name'      => $data['name'],
            'unitPrice' => $data['amount'],
            'quantity'  => $data['qty'],
            'type'      => $data['is_main'] ? 'main' : 'upsells',
            'description' => $data['desc']
        ]);
    }

    /**
     * Checks if the currency supported
     * @param  string $country_code
     * @param  string|null $currency
     * @return bool
     */
    public static function isCurrencySupported(string $country_code, ?string $currency): bool
    {
        $result = false;
        $country = Country::fromIso($country_code);
        if ($country && isset(self::CUR_PER_COUNTRY[$country])) {
            if ($currency && in_array($currency, self::CUR_PER_COUNTRY[$country])) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Checks if the country is supported
     * @param  string $country_code
     * @return bool
     */
    public static function isCountrySupported(string $country_code): bool
    {
        return Country::fromIso($country_code) ? true : false;
    }

    /**
     * Returns TXN status
     * @param   string      $status     Ebanx status
     * @param   bool|null   $is_webhook
     * @return  string
     */
    public static function mapPaymentStatus(string $status, ?bool $is_webhook = false): string
    {
        switch ($status):
            case self::PAYMENT_STATUS_PENDING:
                return Txn::STATUS_AUTHORIZED;
            case self::PAYMENT_STATUS_CONFIRMED:
                return $is_webhook ? Txn::STATUS_APPROVED : Txn::STATUS_CAPTURED;
            default:
                return Txn::STATUS_FAILED;
        endswitch;
    }

    /**
     * Validates webhook
     * @param  Request $req
     * @return array
     */
    public static function validateWebhook(Request $req)
    {
        $sign = $req->header('x-signature-content');
        $content = $req->getContent();
        $notification = new Notification($req->get('operation'), $req->get('notification_type'), explode(',', $req->get('hash_codes')));

        $result = ['status' => false, 'hashes' => []];

        $cert = File::get(config_path("cert/ebanx-notifications-public.pem"));

        $is_sign_valid = openssl_verify($content, \base64_decode($sign), $cert);

        if ($is_sign_valid === 1) {
            $result['status'] = true;
            if ($notification->getNotificationType() === EbanxUtils::UPDATE) {
                $result['hashes'] = $notification->getHashCodes();
            } else {
                logger()->info('Ebanx unprocessed webhook', ['content' => $content]);
            }
        }

        return $result;
    }

    /**
     * Returns payment status info by hash
     * @param  string $hash
     * @return array|null
     */
    public function requestStatusByHash(string $hash): ?array
    {
        $result = ['hash' => $hash, 'status' => Txn::STATUS_FAILED];

        $config = new Config([
            'integrationKey'        => $this->api->key,
            'sandboxIntegrationKey' => $this->environment !== self::ENV_LIVE ? $this->api->key : null,
            'isSandbox'             => $this->environment !== self::ENV_LIVE,
        ]);

        try {
            $res = EBANX($config)->paymentInfo()->findByHash($hash);

            $result = ['hash' => $hash, 'status' => Txn::STATUS_FAILED];

            if ($res['status'] === self::STATUS_OK) {
                $result['value'] = $res['payment']['amount_ext'];
                $result['number'] = $res['payment']['order_number'];
                $result['status'] = self::mapPaymentStatus($res['payment']['status'], true);
                $result['currency'] = $res['payment']['currency_ext'];

                // check if it is refund
                if (!empty($res['payment']['refunds'])) {
                    logger()->warning("Ebanx try to get refunded txn", ['reply' => json_encode($res)]);
                }
            } else {
                logger()->warning("Ebanx cancelled", ['reply' => json_encode($res)]);
            }
        } catch (\Exception $ex) {
            logger()->warning("Ebanx info", ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
        }
        return $result;
    }

    /**
     * Provides payment by card
     * @param array $card
     * @param array $contacts
     * @param array $items
     * @param array $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'number'=>string,
     *   'installments'=>int,
     * ]
     * @return array
     */
    public function payByCard(array $card, array $contacts, array $items, array $details): array
    {
        return $this->pay(
            self::createCardSource($card, $contacts),
            self::createAddress($contacts),
            self::createPerson($contacts),
            array_map(function($item) { return self::createItem($item); }, $items),
            $details
        );
    }

    /**
     * Provides payment by token
     * @param string $token
     * @param array $contact
     * @param array $items
     * @param array $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'number'=>string,
     *   'installments'=>int
     * ]
     * @return array
     */
    public function payByToken(string $token, array $contact, array $items, array $details): array
    {
        return $this->pay(
            self::createTokenSource($token),
            self::createAddress($contact),
            self::createPerson($contact),
            array_map(function($item) { return self::createItem($item); }, $items),
            $details
        );
    }

    /**
     * Refund payment
     * @param  string $hash
     * @param  string $currency
     * @param  float  $amount
     * @param  string $reason
     * @return array
     */
    public function refund(string $hash, string $currency, float $amount, string $reason): array
    {
        $config = new Config([
            'integrationKey'        => $this->api->key,
            'sandboxIntegrationKey' => $this->environment !== self::ENV_LIVE ? $this->api->key : null,
            'isSandbox'             => $this->environment !== self::ENV_LIVE,
            'baseCurrency'          => $currency
        ]);

        $result = ['status' => false];
        try {
            $res = EBANX($config)->refund()->requestByHash($hash, $amount, $reason);

            if ($res['status'] === self::STATUS_OK) {
                $result['status'] = true;
            } else {
                logger()->warning("Ebanx refund", ['res' => $res]);
                $result['errors'] = [($res['status_message'] ?? 'Something went wrong') . " [{$hash}]"];
            }
        } catch (\Exception $ex) {
            logger()->warning("Ebanx refund", ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
            $result['errors'] = [($ex->getMessage() ?? 'Something went wrong') . " [{$hash}]"];
        }
        return $result;
    }

    /**
     * Provides payment
     * @param  Card    $source
     * @param  Address $address
     * @param  Person  $person
     * @param  array   $items Item[]
     * @param  array   $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'number'=>string,
     *   'installments'=>int
     * ]
     * @return array
     */
    private function pay(Card $source, Address $address, Person $person, array $items, array $details): array
    {
        $result = [
            'payment_provider' => PaymentProviders::EBANX,
            'payment_api_id' => (string)$this->api->getIdAttribute(),
            'currency' => $details['currency'],
            'status' => Txn::STATUS_FAILED,
            'hash' => 'fail_' . hrtime(true),
            'value' => $details['amount'],
            'provider_data' => null,
            'redirect_url' => null,
            'is_flagged' => false,
            'payer_id' => null,
            'errors' => null
        ];

        $config = new Config([
            'integrationKey'        => $this->api->key,
            'sandboxIntegrationKey' => $this->environment !== self::ENV_LIVE ? $this->api->key : null,
            'isSandbox'             => $this->environment !== self::ENV_LIVE,
            'baseCurrency'          => $details['currency']
        ]);

        $installments = !empty($details['installments']) ? $details['installments'] : self::INSTALLMENTS_MIN;
        $payment = new Payment([
            'address'               => $address,
            'amountTotal'           => $details['amount'],
            'card'                  => $source,
            'instalments'           => $installments,
            'merchantPaymentCode'   => \uniqid(),
            'orderNumber'           => $details['number'],
            'person'                => $person,
            'items'                 => $items,
            'type'                  => PaymentMethods::CREDITCARD
        ]);

        try {
            $res = EBANX($config, new CreditCardConfig())->create($payment);

            $result['provider_data'] = $res;
            if ($res['status'] === self::STATUS_OK) {
                $result['hash']     = $res['payment']['hash'];
                $result['currency'] = $res['payment']['currency_ext'];
                $result['value']    = $res['payment']['amount_ext'];
                $result['status']   = self::mapPaymentStatus($res['payment']['status']);
                $result['is_flagged'] = $res['payment']['status'] === self::PAYMENT_STATUS_PENDING;
            } else {
                if (!EbanxCodeMapper::getPhrase($res['status_code'])) {
                    $result['fallback'] = true;
                }
                $result['errors'] = [EbanxCodeMapper::toPhrase($res['status_code'])];
                logger()->warning("Ebanx pay", ['res' => $res]);
            }
        } catch (\Exception $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'message' => $ex->getMessage()];
            $result['errors'] = [EbanxCodeMapper::toPhrase()];
            logger()->warning("Ebanx pay", ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
        }
        return $result;
    }

    /**
     * Get address by zipcode
     * @param  string $zipcode
     */
    public function getAddressByZip(string $zipcode): array
    {
        $zipcode = preg_replace('/\D/', '', $zipcode);
        $client = new \GuzzleHttp\Client();

        $url = $this->environment === static::ENV_LIVE ? EbanxClient::LIVE_URL : EbanxClient::SANDBOX_URL;

        $request = $client->request('POST', $url.'ws/zipcode', [
            'form_params' => [
                'integration_key' => $this->api->key,
                'zipcode' => $zipcode,
            ]
        ]);

        $response = $request->getBody()->getContents();
        $response = json_decode($response, true);
        $returnedArray = [];
        if ($response && isset($response['status'])) {
            $returnedArray = [
                'state' => $response['zipcode']['state'] ?? '',
                'city' => $response['zipcode']['city'] ?? '',
                'address' => $response['zipcode']['address'] ?? '',
            ];
        }
        return $returnedArray;
    }
}
