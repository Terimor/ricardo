<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Currency;
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
 * EbanxService class
 */
class EbanxService
{
    use \App\Services\ProviderServiceTrait;

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
     * @var array
     */
    private static $fallback_codes = ['BP-DR-83'];

    /**
     * @var string
     */
    private $environment = self::ENV_LIVE;

    /**
     * EbanxService constructor
     */
    public function __construct()
    {
        $keys = PaymentApi::getAllByProvider(PaymentProviders::EBANX);

        if (empty($keys)) {
            logger()->error("Ebanx configuration needs to check");
        }

        $this->keys = $keys;
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
            'createToken'   => true,
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
     * @param   array $data
     * @return  array Item
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
     * Returns available currency for country
     * @param  string $country_code
     * @param  string|null $currency
     * @return string
     */
    public static function getCurrencyByCountry(string $country_code, ?string $currency): ?string
    {
        $country = Country::fromIso($country_code);
        if ($country && isset(self::CUR_PER_COUNTRY[$country])) {
            if ($currency && \in_array($currency, self::CUR_PER_COUNTRY[$country])) {
                return $currency;
            }
            return Currency::DEF_CUR;
        }
        return Currency::DEF_CUR;
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
     * Returns payment status info by hash
     * @param  string $hash
     * @param  array  $details ['payment_api_id'=>string]
     * @return array|null
     */
    public function requestStatusByHash(string $hash, array $details): ?array
    {
        $result = ['hash'  => $hash, 'status' => Txn::STATUS_FAILED];

        $api = $this->getPaymentApi($details);
        if (empty($api)) {
            logger()->error("Ebanx PaymentApi not found");
            return $result;
        }

        $config = new Config([
            'integrationKey'        => $api->key,
            'sandboxIntegrationKey' => $this->environment !== self::ENV_LIVE ? $api->key : null,
            'isSandbox'             => $this->environment !== self::ENV_LIVE,
        ]);

        try {
            $res = EBANX($config)->paymentInfo()->findByHash($hash);

            $result = ['hash'  => $hash, 'status' => Txn::STATUS_FAILED];

            if ($res['status'] === self::STATUS_OK) {
                $result['number']   = $res['payment']['order_number'];
                $result['currency'] = $res['payment']['currency_ext'];
                $result['value']    = $res['payment']['amount_ext'];
                $result['status']   = self::mapPaymentStatus($res['payment']['status'], true);
            } else {
                logger()->warning("Ebanx cancelled", ['reply' => \json_encode($res)]);
            }
        } catch (\Exception $ex) {
            logger()->error("Ebanx info", ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
        }
        return $result;
    }

    /**
     * Provides payment by card
     * @param  array   $card
     * @param  array   $contact
     * @param  array   $items
     * @param  array   $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'number'=>string,
     *   'installments'=>int,
     *   'payment_api_id'=>?string,
     *   'product_id'=>?string
     * ]
     * @return array
     */
    public function payByCard(array $card, array $contact, array $items, array $details): array
    {
        return $this->pay(
            self::createCardSource($card, $contact),
            self::createAddress($contact),
            self::createPerson($contact),
            array_map(function($item) { return self::createItem($item); }, $items),
            $details
        );
    }

    /**
     * Provides payment by token
     * @param  array   $token
     * @param  array   $contact
     * @param  array   $items
     * @param  array   $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'number'=>string,
     *   'installments'=>int,
     *   'payment_api_id'=>?string,
     *   'product_id'=>?string
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
     *   'installments'=>int,
     *   'payment_api_id'=>?string,
     *   'product_id'=>?string
     * ]
     * @return array
     */
    private function pay(Card $source, Address $address, Person $person, array $items, array $details): array
    {
        $result = [
            'is_flagged'        => false,
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::EBANX,
            'payment_api_id'    => null,
            'hash'              => "fail_" . UtilsService::randomString(16),
            'payer_id'          => null,
            'provider_data'     => null,
            'redirect_url'      => null,
            'errors'            => null,
            'token'             => null
        ];

        $api = $this->getPaymentApi($details);
        if (empty($api)) {
            logger()->error("Ebanx PaymentApi not found [{$details['number']}]");
            return $result;
        }

        $result['payment_api_id'] = (string)$api->getIdAttribute();

        $config = new Config([
            'integrationKey'        => $api->key,
            'sandboxIntegrationKey' => $this->environment !== self::ENV_LIVE ? $api->key : null,
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
                $result['hash']             = $res['payment']['hash'];
                $result['currency']         = $res['payment']['currency_ext'];
                $result['value']            = $res['payment']['amount_ext'];
                $result['status']           = self::mapPaymentStatus($res['payment']['status']);
                $result['is_flagged']       = $res['payment']['status'] === self::PAYMENT_STATUS_PENDING ? true : false;
                $result['token']            = $res['payment']['token'] ?? null;
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
            logger()->error("Ebanx pay", ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
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
        $sign = $req->header('x-signature-content');
        $content = $req->getContent();
        $notification = new Notification($req->get('operation'), $req->get('notification_type'), explode(',', $req->get('hash_codes')));

        $result = ['status' => false];

        $cert = File::get(\config_path("cert/ebanx-notifications-public.pem"));

        $is_sign_valid = \openssl_verify($content, \base64_decode($sign), $cert);

        if ($is_sign_valid && EbanxUtils::isValidNotification($notification)) {
            $result = ['status' => true, 'hashes' => $notification->getHashCodes()];
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

        $api = $this->getPaymentApi([]);
        if (empty($api)) {
            logger()->error("Ebanx PaymentApi not found");
            return [];
        }

        $request = $client->request('POST', $url.'ws/zipcode', [
            'form_params' => [
                'integration_key' => $api->key,
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
