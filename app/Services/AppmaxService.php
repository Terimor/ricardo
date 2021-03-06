<?php

namespace App\Services;

use App\Models\PaymentApi;
use App\Models\Txn;
use App\Models\Setting;
use App\Constants\PaymentProviders;
use App\Mappers\AppmaxCodeMapper;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;

/**
 * Class AppmaxService
 * @package App\Services
 */
class AppmaxService extends ProviderService
{

    const ENV_LIVE      = 'live';
    const ENV_SANDBOX   = 'sandbox';

    const STATUS_APPROVED   = 'aprovado';
    const STATUS_AUTHORIZED = 'autorizado';

    const TYPE_REFUND_FULL  = 'total';
    const TYPE_REFUND_PART  = 'partial';

    const WEBHOOK_EVENT_ORDER_APPROVED = 'OrderApproved';
    const WEBHOOK_EVENT_ORDER_PAID = 'OrderPaid';

    const INSTALLMENTS_MIN = 1;

    const COUNTRY_CURRENCY = [
        'br' => ['BRL']
    ];

    /**
     * @var string
     */
    private string $endpoint;

    /**
     * AppmaxService constructor
     * @param PaymentApi $api
     */
    public function __construct(PaymentApi $api)
    {
        parent::__construct($api);
        $env = Setting::getValue('appmax_environment', self::ENV_LIVE);
        $this->endpoint = 'https://' . ($env === self::ENV_LIVE ? 'admin' : 'sandbox') . '.appmax.com.br/api/v3/';
    }

    /**
     * Returns Card object
     * @param  array $card
     * @param  array $contacts
     * @param  array $details ['installments' => int,'document_number' => string]
     * @return array
     */
    public static function createCardObj(array $card, array $contacts, array $details = []): array
    {
        return [
            'name'      => $contacts['first_name'] . ' ' . $contacts['last_name'],
            'month'     => $card['month'],
            'year'      => substr($card['year'], 2),
            'cvv'       => $card['cvv'],
            'number'    => $card['number'],
            'installments' => !empty($details['installments']) ? $details['installments'] : self::INSTALLMENTS_MIN,
            'document_number' => $details['document_number']
        ];
    }

    /**
     * Returns CardHolder object
     * @param  array $contacts
     * @return array
     */
    public static function createCustomerObj(array $contacts): array
    {

        return [
            'address_street'    => $contacts['street'],
            'address_city'      => $contacts['city'],
            'address_state'     => $contacts['state'] ?? '',
            'address_street_number'     => $contacts['building'] ?? '',
            'address_street_complement' => $contacts['complement'] ?? '',
            'address_street_district'   => $contacts['district'] ?? '',
            'email'     => $contacts['email'],
            'firstname' => $contacts['first_name'],
            'lastname'  => $contacts['last_name'],
            'postcode'  => preg_replace('/\W/','', $contacts['zip']),
            'telephone' => is_array($contacts['phone']) ? $contacts['phone']['number'] : substr($contacts['phone'], 2, 11)
        ];
    }

    /**
     * Checks if the currency is supported
     * @param  string $country_code
     * @param  string $currency
     * @return bool
     */
    public static function isCurrencySupported(string $country_code, string $currency): bool
    {
        $result = false;
        if (isset(self::COUNTRY_CURRENCY[$country_code])) {
            $result = in_array($currency, self::COUNTRY_CURRENCY[$country_code]);
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
        return isset(self::COUNTRY_CURRENCY[$country_code]);
    }

    /**
     * Provides payment by card
     * @param  array   $card
     * @param  array   $contacts
     * @param  array   $items
     * @param  array   $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'installments'=>int,
     *   'document_number'=>string
     * ]
     * @return array
     */
    public function payByCard(array $card, array $contacts, array $items, array $details): array
    {
        $customer_id = $this->requestCustomerId($contacts);
        $reply = $this->pay(
            self::createCardObj($card, $contacts, $details),
            array_merge(
                $details,
                [
                    'customer_id' => $customer_id,
                    'order_id' => $this->requestOrderId($items, $contacts, array_merge($details, ['customer_id' => $customer_id]))
                ]
            )
        );

        if ($reply['status'] !== Txn::STATUS_CAPTURED) {
            $reply['fallback'] = true;
        }

        return $reply;
    }

    /**
     * Returns Item object
     * @param   array $item
     * @return  array
     */
    public static function createItemObj(array $item): array
    {
        return [
            'sku'   => $item['sku'],
            'name'  => $item['name'],
            'price' => $item['amount'],
            'value' => $item['amount'],
            'qty'   => 1, //$item['qty'],
            'image' => $item['image'],
            'description' => $item['desc']
        ];
    }

    /**
     * Returns Customer
     * @param  array $contacts
     * @return string|null
     */
    private function requestCustomerId($contacts): ?string
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = null;
        try {
            $res = $client->post('customer', [
                'json' => array_merge(['access-token' => $this->api->secret], self::createCustomerObj($contacts))
            ]);

            $body = json_decode($res->getBody(), true);

            if ($body['success']) {
                $result = (string)$body['data']['id'];
            } else {
                logger()->info("Appmax customer", ['res' => (string)$res->getBody()]);
            }
        } catch (GuzzReqException $ex) {
            logger()->warning("Appmax customer", [
                'res' => $ex->hasResponse() ? (string)$ex->getResponse()->getBody() : null
            ]);
        }

        return $result;
    }

    /**
     * Returns order
     * @param  array  $items
     * @param  array  $contacts
     * @param  array  $details ['amount'=>float, 'customer_id'=>string]
     * @return string|null
     */
    private function requestOrderId(array $items, array $contacts, array $details): ?string
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = null;

        try {
            $res = $client->post('order', [
                'json' => [
                    'access-token' => $this->api->secret,
                    'total' => $details['amount'],
                    'ip'    => $contacts['ip'],
                    'shipping'  => 0,
                    'discount'  => 0,
                    'products'  => array_map(function($item) { return self::createItemObj($item); }, $items),
                    'customer_id'   => $details['customer_id'],
                    'freight_type'  => 'PAC'
                ]
            ]);

            $body = json_decode($res->getBody(), true);

            if ($body['success']) {
                $result = (string)$body['data']['id'];
            } else {
                logger()->info("Appmax order", ['res' => (string)$res->getBody()]);
            }
        } catch (GuzzReqException $ex) {
            logger()->warning("Appmax order", [
                'res' => $ex->hasResponse() ? (string)$ex->getResponse()->getBody() : null
            ]);
        }

        return $result;
    }

    /**
     * Provides payment
     * @param  array   $source
     * @param  array   $details
     * [
     *  'currency'=>string,
     *  'amount'=>float,
     *  'customer_id'=>string,
     *  'order_id'=>string
     * ]
     * @return array
     */
    private function pay(array $source, array $details): array
    {
        $result = [
            'is_flagged'        => false,
            'fallback'          => false,
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::APPMAX,
            'hash'              => $details['order_id'] ?? ('fail_' . hrtime(true)),
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'payer_id'          => $details['customer_id'] ?? null,
            'provider_data'     => null,
            'errors'            => null
        ];

        if (!$details['order_id'] || !$details['customer_id']) {
            $result['errors'] = [AppmaxCodeMapper::toPhrase()];
            return $result;
        }

        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        try {
            $res = $client->post('payment/credit-card', [
                'json' => [
                    'access-token' => $this->api->secret,
                    'cart' => ['order_id' => $details['order_id']],
                    'customer' => ['customer_id' => $details['customer_id']],
                    'payment' => ['CreditCard' => $source]
                ]
            ]);

            $body = json_decode($res->getBody(), true);

            if ($body['success']) {
                $result['status'] = Txn::STATUS_CAPTURED;
            } else {
                $result['errors'] = [AppmaxCodeMapper::toPhrase()];
                logger()->info("Appmax pay", ['res' => $res->getBody()]);
            }
            $result['provider_data'] = (string)$res->getBody();
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? (string)$ex->getResponse()->getBody() : null;

            $result['errors'] = [AppmaxCodeMapper::toPhrase()];
            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => $res];

            if ($res) {
                $body = json_decode($res, true);
                if (!empty($body)) {
                    $result['errors'] = [AppmaxCodeMapper::toPhrase($body['text'] ?? null)];
                }
            }

            logger()->warning("Appmax pay", ['res' => $result['provider_data']]);
        }
        return $result;
    }

    /**
     * Refunds payment
     * @param  string $hash
     * @param  string $type
     * @param  float  $amount
     * @return array
     */
    public function refund(string $hash, string $type, float $amount): array
    {
        $client = new GuzzHttpCli([
            'base_uri' => $this->endpoint,
            'headers' => ['Accept'  => 'application/json']
        ]);

        $result = ['status' => false, 'errors' => ["Something went wrong. TXN [{$hash}]"]];
        try {
            $res = $client->post('refund', [
                'json' => [
                    'access-token' => $this->api->secret,
                    'order_id' => $hash,
                    'refund_type' => $type,
                    'refund_amount' => $amount
                ]
            ]);

            $body = json_decode($res->getBody(), true);

            if ($body['success']) {
                $result['status'] = true;
                unset($result['errors']);
            } else {
                logger()->info("Appmax refund", ['res' => $res->getBody()]);
            }
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? (string)$ex->getResponse()->getBody() : null;

            logger()->warning("Appmax refund", ['res' => $res]);

            if ($res) {
                $body = json_decode($res, true);
                if (!empty($body)) {
                    $result['errors'] = [($body['text'] ?? "Something went wrong") . " TXN [{$hash}]"];
                }
            }
        }
        return $result;
    }

    /**
     * Validates webhook
     * @param  string $event
     * @param  array $data
     * @return array
     */
    public function validateWebhook(string $event, array $data): array
    {
        $result = ['status' => false];
        if ($event === self::WEBHOOK_EVENT_ORDER_APPROVED && $data['status'] === self::STATUS_APPROVED) {
            $result = [
                'status' => true,
                'txn' => [
                    'hash'      => (string)$data['id'],
                    'status'    => Txn::STATUS_APPROVED,
                    'value'     => preg_replace('/[^\d.]/', '', $data['total'])
                ]
            ];
        } else {
            logger()->info("Unprocessed webhook [{$event}]", ['data' => $data]);
        }

        return $result;
    }
}
