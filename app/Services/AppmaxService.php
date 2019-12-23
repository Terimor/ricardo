<?php

namespace App\Services;

use App\Models\PaymentApi;
use App\Models\Txn;
use App\Models\Setting;
use App\Constants\PaymentMethods;
use App\Constants\PaymentProviders;
use App\Mappers\AppmaxCodeMapper;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client as GuzzHttpCli;
use GuzzleHttp\Exception\RequestException as GuzzReqException;

/**
 * AppmaxService class
 */
class AppmaxService
{
    use ProviderServiceTrait;

    const ENV_LIVE      = 'live';
    const ENV_SANDBOX   = 'sandbox';

    const STATUS_APPROVED = 'aprovado';

    const WEBHOOK_EVENT_ORDER_PAID = 'OrderPaid';

    const INSTALLMENTS_MIN = 1;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var \App\Models\PaymentApi
     */
    private $api;

    /**
     * AppmaxService constructor
     * @param array $details ['product_id'=>?string,'payment_api_id'=>?string]
     */
    public function __construct(array $details = [])
    {
        $this->keys = PaymentApi::getAllByProvider(PaymentProviders::APPMAX);

        if (empty($this->keys)) {
            logger()->error("Appmax configuration needs to check");
        }

        $this->api = $this->getPaymentApi($details);

        $env = Setting::getValue('appmax_environment', self::ENV_LIVE);

        $this->endpoint = 'https://' . ($env === self::ENV_LIVE ? 'prod' : 'sandbox') . '.appmax.com.br/api/v3/';
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
        $phone = $contacts['phone'];
        if (\gettype($contacts['phone']) === 'array') {
            $phone = $contacts['phone']['country_code'] . $contacts['phone']['number'];
        }
        $result = [
            'address_street'    => $contacts['street'],
            'address_city'      => $contacts['city'],
            'address_state'     => $contacts['state'],
            'address_street_number' => $contacts['district'] ?? '',
            // 'address_street_complement' => '',
            // 'address_street_district' => 'Centro',
            'email'     => $contacts['email'],
            'firstname' => $contacts['first_name'],
            'lastname'  => $contacts['last_name'],
            'postcode'  => $contacts['zip'],
            'telephone' => $phone
        ];

        return $result;
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
     *   'order_id'=>?string,
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

        if ($reply['status'] === Txn::STATUS_CAPTURED && !empty($details['order_id'])) {
            $reply['token'] = self::encrypt(json_encode($card), $details['order_id']);
        }

        return $reply;
    }

    /**
     * Provides payment by card
     * @param  string   $token
     * @param  array   $contacts
     * @param  array   $items
     * @param  array   $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'order_id'=>string,
     *   'installments' => int,
     *   'document_number' => string
     * ]
     * @return array
     */
    public function payByToken(string $token, array $contacts, array $items, array $details): array
    {
        $cardjs = self::decrypt($token, $details['order_id']);
        $details['order_id'] = null;
        return $this->payByCard(json_decode($cardjs, true), $contacts, $items, $details);
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
                'json' => \array_merge(['access-token' => $this->api->secret], self::createCustomerObj($contacts))
            ]);

            $body = \json_decode($res->getBody(), true);

            if ($body['success']) {
                $result = (string)$body['data']['id'];
            } else {
                logger()->info("Appmax customer", ['res' => $res->getBody()]);
            }
        } catch (GuzzReqException $ex) {
            logger()->error("Appmax customer", [
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $ex->hasResponse() ? $ex->getResponse()->getBody() : null
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

            $body = \json_decode($res->getBody(), true);

            if ($body['success']) {
                $result = (string)$body['data']['id'];
            } else {
                logger()->info("Appmax order", ['res' => $res->getBody()]);
            }
        } catch (GuzzReqException $ex) {
            logger()->error("Appmax order", [
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $ex->hasResponse() ? $ex->getResponse()->getBody() : null
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
            'fee_usd'           => 0,
            'is_flagged'        => false,
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::APPMAX,
            'hash'              => $details['order_id'] ?? "fail_" . UtilsService::randomString(16),
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'payer_id'          => $details['customer_id'] ?? null,
            'provider_data'     => null,
            'token'             => null,
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

            $body = \json_decode($res->getBody(), true);

            if ($body['success']) {
                $result['status'] = Txn::STATUS_CAPTURED;
            } else {
                logger()->info("Appmax order", ['res' => $res->getBody()]);
            }
            $result['provider_data'] = (string)$res->getBody();
        } catch (GuzzReqException $ex) {
            $res = $ex->hasResponse() ? $ex->getResponse() : null;
            $result['provider_data'] = ['code' => $ex->getCode(), 'res' => $res ? $res->getBody() : null];
            logger()->error("Appmax pay", [
                'request'   => Psr7\str($ex->getRequest()),
                'response'  => $res
            ]);
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
        if ($event === self::WEBHOOK_EVENT_ORDER_PAID) {
            $result = [
                'status' => true,
                'txn' => [
                    'fee_usd'   => 0,
                    'hash'      => $data['id'],
                    'status'    => $data['status'] === self::STATUS_APPROVED ? Txn::STATUS_APPROVED : Txn::STATUS_FAILED,
                    'value'     => preg_replace('/[^\d.]/', '', $data['total'])
                ]
            ];
        } else {
            logger()->info("Unprocessed webhook [{$event}]", ['data' => $data]);
        }

        return $result;
    }
}
