<?php

namespace App\Services;

use App\Models\OdinOrder;
use App\Models\Setting;
use App\Models\Txn;
use App\Services\CurrencyService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Mappers\EbanxCodeMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Ebanx\Benjamin\Models\Address;
use Ebanx\Benjamin\Models\Card;
use Ebanx\Benjamin\Models\Configs\Config;
use Ebanx\Benjamin\Models\Configs\CreditCardConfig;
use Ebanx\Benjamin\Models\Country;
use Ebanx\Benjamin\Models\Currency;
use Ebanx\Benjamin\Models\Payment;
use Ebanx\Benjamin\Models\Person;
use Ebanx\Benjamin\Models\Item;
use Ebanx\Benjamin\Models\Notification;
use Ebanx\Benjamin\Util\Http as EbanxUtils;

/**
 * EbanxService class
 */
class EbanxService
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
        Country::ARGENTINA  => [Currency::ARS, Currency::USD],
        Country::BRAZIL     => [Currency::BRL, Currency::USD, Currency::EUR],
        Country::BOLIVIA    => [Currency::BOB, Currency::USD],
        Country::CHILE      => [Currency::CLP, Currency::USD, Currency::EUR],
        Country::COLOMBIA   => [Currency::COP, Currency::USD, Currency::EUR],
        Country::ECUADOR    => [Currency::USD],
        Country::MEXICO     => [Currency::MXN, Currency::USD],
        Country::PERU       => [Currency::PEN, Currency::USD]
    ];

    /**
     * @var string
     */
    private $integration_key;

    /**
     * @var string
     */
    private $sandbox_integration_key;

    /**
     * @var string
     */
    private $environment = self::ENV_LIVE;

    /**
     * EbanxService constructor
     */
    public function __construct()
    {
        $integration_key = Setting::getValue('ebanx_integration_key');
        $sandbox_integration_key = Setting::getValue('ebanx_sandbox_integration_key', null);
        $environment = Setting::getValue('ebanx_api_environment', self::ENV_LIVE);

        if (!$integration_key) {
            logger()->error("Ebanx integration_key not found");
        }

        $this->integration_key = $integration_key;
        $this->sandbox_integration_key = $sandbox_integration_key;
        $this->environment = $environment;
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
            'state'     => $contact['state'],
            'zipcode'   => $contact['zip'],
            'streetNumber' => $contact['district'] ?? '' // maybe undefined
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
            'type'          => PaymentService::METHOD_CREDITCARD
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
            return Currency::USD;
        }
        return Currency::USD;
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
     * @return array|null
     */
    public function requestStatusByHash(string $hash): ?array
    {
        $config = new Config([
            'integrationKey'        => $this->integration_key,
            'sandboxIntegrationKey' => $this->sandbox_integration_key,
            'isSandbox'             => $this->environment !== self::ENV_LIVE
        ]);

        $result = null;

        try {
            $res = EBANX($config)->paymentInfo()->findByHash($hash);

            logger()->info('Ebanx query', ['reply' => \json_encode($res)]);

            $result = ['hash'  => $hash, 'status' => Txn::STATUS_FAILED];

            if ($res['status'] === self::STATUS_OK) {
                $result['number']   = $res['payment']['order_number'];
                $result['currency'] = $res['payment']['currency_ext'];
                $result['fee']      = $res['payment']['amount_iof'];
                $result['value']    = $res['payment']['amount_ext'];
                $result['status']   = self::mapPaymentStatus($res['payment']['status'], true);
            } else {
                logger()->error("Ebanx cancelled", ['reply' => \json_encode($res)]);
            }
        } catch (\Exception $ex) {
            logger()->error("Ebanx query", ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);

        }
        return $result;
    }

    /**
     * Provides payment by card
     * @param  array   $card
     * @param  array   $contact
     * @param  array   $items
     * @param  array   $order_details ['currency'=>string,'amount'=>float,'number'=>string,'installments'=>int]
     * @return array
     */
    public function payByCard(array $card, array $contact, array $items, array $order_details): array
    {
        return $this->pay(
            self::createCardSource($card, $contact),
            self::createAddress($contact),
            self::createPerson($contact),
            array_map(function($item) { return self::createItem($item); }, $items),
            $order_details
        );
    }

    /**
     * Provides payment by token
     * @param  array   $token
     * @param  array   $contact
     * @param  array   $items
     * @param  array   $order_details ['currency'=>string,'amount'=>float,'number'=>string,'installments'=>int]
     * @return array
     */
    public function payByToken(string $token, array $contact, array $items, array $order_details): array
    {
        return $this->pay(
            self::createTokenSource($token),
            self::createAddress($contact),
            self::createPerson($contact),
            array_map(function($item) { return self::createItem($item); }, $items),
            $order_details
        );
    }

    /**
     * Provides payment
     * @param  Card    $source
     * @param  Address $address
     * @param  Person  $person
     * @param  array   $items Item[]
     * @param  array   $order_details ['currency'=>string,'amount'=>float,'number'=>string,'installments'=>int]
     * @return array
     */
    private function pay(Card $source, Address $address, Person $person, array $items, array $order_details): array
    {
        $config = new Config([
            'integrationKey'        => $this->integration_key,
            'sandboxIntegrationKey' => $this->sandbox_integration_key,
            'isSandbox'             => $this->environment !== self::ENV_LIVE,
            'baseCurrency'          => $order_details['currency']
        ]);

        $payment = new Payment([
            'address'               => $address,
            'amountTotal'           => $order_details['amount'],
            'card'                  => $source,
            'instalments'           => $order_details['installments'] ?? self::INSTALLMENTS_MIN,
            'merchantPaymentCode'   => \uniqid(),
            'orderNumber'           => $order_details['number'],
            'person'                => $person,
            'items'                 => $items,
            'type'                  => PaymentService::METHOD_CREDITCARD
        ]);

        $result = [
            'fee'               => 0,
            'is_flagged'        => false,
            'currency'          => $order_details['currency'],
            'value'             => $order_details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentService::PROVIDER_EBANX,
            'payment_method'    => PaymentService::METHOD_CREDITCARD,
            'hash'              => null,
            'payer_id'          => null,
            'provider_data'     => null,
            'redirect_url'      => null,
            'errors'            => null,
            'token'             => null
        ];

        try {
            $res = EBANX($config, new CreditCardConfig())->create($payment);

            $result['provider_data'] = $res;
            if ($res['status'] === self::STATUS_OK) {
                $result['hash']             = $res['payment']['hash'];
                $result['currency']         = $res['payment']['currency_ext'];
                $result['value']            = $res['payment']['amount_ext'];
                $result['fee']              = $res['payment']['amount_iof'];
                $result['status']           = self::mapPaymentStatus($res['payment']['status']);
                $result['is_flagged']       = $res['payment']['status'] === self::PAYMENT_STATUS_PENDING ? true : false;
                $result['token']            = $res['payment']['token'] ?? null;
            } else {
                $result['errors'] = [EbanxCodeMapper::toPhrase((string)$res['status_code'])];
            }
        } catch (\Exception $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'message' => $ex->getMessage()];
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
}
