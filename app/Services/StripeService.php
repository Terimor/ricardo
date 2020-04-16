<?php

namespace App\Services;

use App\Models\PaymentApi;
use App\Models\Txn;
use App\Constants\PaymentProviders;
use App\Mappers\StripeCodeMapper;
use App\Mappers\StripeAmountMapper;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Customer;
use Stripe\Refund;
use Stripe\PaymentMethod;
use Stripe\PaymentIntent;

/**
 * Class StripeService
 * @package App\Services
 */
class StripeService
{
    use ProviderServiceTrait;

    const REFUND_STATUS_SUCCESS = 'succeeded';
    const REFUND_STATUS_FAILED  = 'failed';
    const REFUND_STATUS_PENDING = 'pending';

    const PI_STATUS_ACTION      = 'requires_action';
    const PI_STATUS_SUCCESSED   = 'succeeded';
    const PI_STATUS_CANCELED    = 'canceled';
    const PI_STATUS_PROCESSING  = 'processing';

    /**
     * StripeService constructor
     * @param PaymentApi $api
     */
    public function __construct(PaymentApi $api)
    {
        Stripe::setApiKey($api->secret);
        $this->api = $api;
    }

    /**
     * Checks if the currency is supported
     * @param  string $currency
     * @return bool
     */
    public static function isCurrencySupported(string $currency): bool
    {
        $result = false;
        if (StripeAmountMapper::toProvider(1, $currency)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Returns PaymentIntent from webhook payload
     * @param  string $payload
     * @return PaymentIntent|null
     */
    public static function extractPaymentIntent(string $payload): ?PaymentIntent
    {
        $pi = null;
        try {
            $event = Event::constructFrom(json_decode($payload, true));
        } catch(\UnexpectedValueException $e) {
            return $pi;
        }

        switch ($event->type):
            case 'payment_intent.succeeded':
            case 'payment_intent.canceled':
                $pi = $event->data->object; // contains a PaymentIntent
                break;
            default:
                logger()->warning('Stripe unknown event', ['payload' => $payload]);
        endswitch;

        return $pi;
    }

    /**
     * Returns template of payment
     * @param  array $details ['currency'=>string,'amount'=>float]
     * @return array
     */
    private function createReplyTmpl(array $details): array
    {
        return [
            'is_flagged'        => false,
            'fallback'          => false,
            'currency'          => $details['currency'],
            'value'             => $details['amount'],
            'status'            => Txn::STATUS_FAILED,
            'payment_provider'  => PaymentProviders::STRIPE,
            'hash'              => "fail_" . UtilsService::randomString(16),
            'payment_api_id'    => (string)$this->api->getIdAttribute(),
            'payer_id'          => null,
            'provider_data'     => null,
            'token'             => null,
            'errors'            => null
        ];
    }

    /**
     * Creates a new customer
     * @param array $contacts
     * @return Customer
     * @throws ApiErrorException
     */
    private function createCustomer(array $contacts): Customer
    {
        $phone = is_array($contacts['phone']) ? implode('', $contacts['phone']) : $contacts['phone'];

        return Customer::create([
            'email' => $contacts['email'],
            'name'  => $contacts['first_name'] . ' ' . $contacts['last_name'],
            'phone' => $phone,
            'address' => [
                'city'  => $contacts['city'],
                'line1' => $contacts['street'],
                'state' => $contacts['state'] ?? null,
                'country' => $contacts['country'],
                'postal_code' => $contacts['zip']
            ],
            'shipping' => [
                'name'  => $contacts['first_name'] . ' ' . $contacts['last_name'],
                'phone' => $phone,
                'address' => [
                    'city'  => $contacts['city'],
                    'line1' => $contacts['street'],
                    'state' => $contacts['state'] ?? null,
                    'country' => $contacts['country'],
                    'postal_code' => $contacts['zip']
                ],
            ]
        ]);
    }

    /**
     * Returns PaymentMethod
     * @param array $card
     * @param array $contacts
     * @return PaymentMethod
     * @throws ApiErrorException
     */
    private function createCardPaymentMethod(array $card, array $contacts): PaymentMethod
    {
        return PaymentMethod::create([
            'type' => 'card',
            'billing_details' => [
                'email' => $contacts['email'],
                'name'  => $contacts['first_name'] . ' ' . $contacts['last_name'],
                'phone' => is_array($contacts['phone']) ? implode('', $contacts['phone']) : $contacts['phone'],
                'address' => [
                    'city'  => $contacts['city'],
                    'line1' => $contacts['street'],
                    'state' => $contacts['state'] ?? null,
                    'country' => $contacts['country'],
                    'postal_code' => $contacts['zip']
                ]
            ],
            'card' => [
                'number'    =>  $card['number'],
                'exp_month' => $card['month'],
                'exp_year'  => $card['year'],
                'cvc'   => $card['cvv'],
            ],
        ]);
    }

    /**
     * Prepares payment response
     * @param PaymentIntent $pi
     * @param array $data
     * @return array
     */
    public function preparePaymentResponse(PaymentIntent $pi, array $data): array
    {
        $data['provider_data'] = $pi->toArray();
        $data['hash'] = $pi->id;
        $data['payer_id'] = $pi->customer;

        switch ($pi->status):
            case self::PI_STATUS_ACTION:
                if (optional($pi->next_action)['type'] === 'redirect_to_url') {
                    $data['status'] = Txn::STATUS_AUTHORIZED;
                    $data['redirect_url'] = $pi->next_action['redirect_to_url']['url'];
                }
                break;
            case self::PI_STATUS_SUCCESSED:
                $data['status'] = Txn::STATUS_APPROVED;
                break;
            case self::PI_STATUS_CANCELED:
            default:
                $data['status'] = Txn::STATUS_FAILED;
                $data['errors'] = [
                    StripeCodeMapper::toPhrase(
                        optional($pi->last_payment_error)['decline_code'] ?? null,
                        optional($pi->last_payment_error)['code']
                    )
                ];
                $data['fallback'] = StripeCodeMapper::isFallback(
                    optional($pi->last_payment_error)['decline_code'] ?? null,
                    optional($pi->last_payment_error)['code']
                );
        endswitch;

        return $data;
    }

    /**
     * Returns payment info by PaymentIntent ID
     * @param  string $pi_id
     * @return array|null
     */
    public function getPaymentInfo(string $pi_id): array
    {
        $result = ['status' => false, 'txn' => ['hash' => $pi_id, 'status' => Txn::STATUS_FAILED]];

        try {
            $pi = PaymentIntent::retrieve($pi_id);

            if (!empty($pi)) {
                $result['status'] = true;
                $result['txn']['number'] = $pi->metadata['order_number'];
                $result['txn']['value'] = StripeAmountMapper::fromProvider($pi->amount, $pi->currency);
            }

            switch ($pi->status):
                case self::PI_STATUS_PROCESSING:
                case self::PI_STATUS_ACTION:
                    $result['txn']['status'] = Txn::STATUS_AUTHORIZED;
                    break;
                case self::PI_STATUS_SUCCESSED:
                    $result['txn']['status'] = Txn::STATUS_APPROVED;
                    break;
                case self::PI_STATUS_CANCELED:
                    $result['txn']['errors'] = [
                        StripeCodeMapper::toPhrase(
                            optional($pi->last_payment_error)['decline_code'] ?? null,
                            optional($pi->last_payment_error)['code']
                        )
                    ];
                    $result['txn']['fallback'] = StripeCodeMapper::toPhrase(
                        optional($pi->last_payment_error)['decline_code'] ?? null,
                        optional($pi->last_payment_error)['code']
                    );
                    break;
            endswitch;

        } catch (ApiErrorException $ex) {
            logger()->warning('PaymentIntent retrieve', ['code' => $ex->getHttpStatus(), 'message' => $ex->getHttpBody()]);
            $result['txn']['errors'] = [
                StripeCodeMapper::toPhrase(optional($ex->getError())->decline_code ?? null, optional($ex->getError())->code)
            ];
            $result['txn']['fallback'] = StripeCodeMapper::toPhrase(
                optional($ex->getError())->decline_code ?? null,
                optional($ex->getError())->code
            );
        } catch (Exception $ex) {
            logger()->warning('PaymentIntent retrieve', ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
            $result['txn']['errors'] = [StripeCodeMapper::toPhrase()];
        }
        return $result;
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
     *   'installments'=>?int,
     *   'order_id'=>string,
     *   'order_number'=>string,
     *   'billing_descriptor'=>string
     * ]
     * @return array
     */
    public function payByCard(array $card, array $contacts, array $details): array
    {
        $reply = $this->createReplyTmpl($details);
        try {
            $customer = $this->createCustomer($contacts);

            $pi = $this->pay($this->createCardPaymentMethod($card, $contacts), $customer->id, $details);

            $reply['token'] = self::encrypt(json_encode($card), $details['order_id']);

            $reply = $this->preparePaymentResponse($pi, $reply);
        } catch (ApiErrorException $ex) {
            if ($pi = optional($ex->getError())->payment_intent) {
                $reply = $this->preparePaymentResponse($pi, $reply);
            }
            logger()->warning('Stripe pay-by-card', ['code' => $ex->getHttpStatus(), 'message' => $ex->getHttpBody()]);
        } catch (\Exception $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'message' => $ex->getMessage()];
            $reply['errors'] = [StripeCodeMapper::toPhrase()];
            logger()->warning('Stripe pay-by-card', $result['provider_data']);
        }

        return $reply;
    }

    /**
     * Provides payment by saved card before
     * @param  string  $customer_id
     * @param  array   $details
     * [
     *   'currency'=>string,
     *   'amount'=>float,
     *   'installments'=>?int,
     *   'order_id'=>string,
     *   'order_number'=>string,
     *   'billing_descriptor'=>string
     * ]
     * @return array
     */
    public function payBySavedCard(string $customer_id, array $details): array
    {
        $reply = $this->createReplyTmpl($details);
        try {
            $pms = PaymentMethod::all(['customer' => $customer_id, 'type' => 'card' ], ['limit' => 1 ]);

            if (!empty($pms->data)) {
                // get first PaymentMethod
                $pi = $this->pay($pms->data[0], $customer_id, $details);
                $reply = $this->preparePaymentResponse($pi, $reply);
            } else {
                $reply['provider_data'] = ['message' => "Customer [{$customer_id}]. PaymentMethod not found"];
                $reply['errors'] = [StripeCodeMapper::toPhrase()];
            }
        } catch (ApiErrorException $ex) {
            if ($pi = optional($ex->getError())->payment_intent) {
                $reply = $this->preparePaymentResponse($pi, $reply);
            }
            logger()->warning('Stripe pay-by-saved-card', ['code' => $ex->getHttpStatus(), 'message' => $ex->getHttpBody()]);
        } catch (\Exception $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'message' => $ex->getMessage()];
            $reply['errors'] = [StripeCodeMapper::toPhrase()];

            logger()->warning('Stripe pay by saved card', $reply['provider_data']);
        }

        return $reply;
    }

    /**
     * Returns Stripe PaymentIntent
     * @param PaymentMethod $pm
     * @param string $customer_id
     * @param array $details
     * [
     *   '3ds'=>?boolean,
     *   'currency'=>string,
     *   'amount'=>float,
     *   'installments'=>?int,
     *   'order_id'=>string,
     *   'order_number'=>string,
     *   'billing_descriptor'=>string
     * ]
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    private function pay(PaymentMethod $pm, string $customer_id, array $details): PaymentIntent
    {
        $pm_opts = ['card' => ['request_three_d_secure' => 'any']];
        if (empty($details['3ds'])) {
            $pm_opts['card']['request_three_d_secure'] = 'automatic';
        }

        if (!empty($details['installments'])) {
            $pm_opts['card']['installments'] = [
                'enabled' => true,
                'plan' => [
                    'count' => $details['installments'],
                    'type'  => 'fixed_count',
                    'interval' => 'month'
                ]
            ];
        }
        return PaymentIntent::create([
            'amount' => StripeAmountMapper::toProvider($details['amount'], $details['currency']),
            'confirm' => true,
            'customer' => $customer_id,
            'currency' => strtolower($details['currency']),
            'metadata' => ['order_number' => $details['order_number']],
            'receipt_email' => $pm->billing_details['email'],
            'statement_descriptor' => "{$details['billing_descriptor']}/+18887438103",
            'statement_descriptor_suffix' => 'support@gizmoshop.net',
            'payment_method' => $pm->id,
            'payment_method_types' => ['card'],
            'payment_method_options' => $pm_opts,
            'save_payment_method' => true,
            'return_url' => 'https://' . request()->getHttpHost() . "/stripe-3ds/{$details['order_id']}",
            'shipping' => [
                'address' => $pm->billing_details['address']->toArray(),
                'name' => $pm->billing_details['name'],
                'phone' => $pm->billing_details['phone']
            ]
        ]);
    }

    /**
     * Invokes refund
     * @param string $pi_id
     * @param string $currency
     * @param float|null $amount
     * @return array
     */
    public function refund(string $pi_id, string $currency, ?float $amount = null): array
    {
        $result = ['status' => false];
        try {
            $body = ['payment_intent' => $pi_id];
            if ($amount) {
                $body['amount'] = StripeAmountMapper::toProvider($amount, $currency);
            }

            $r = Refund::create($body);

            switch ($r->status):
                case self::REFUND_STATUS_SUCCESS:
                    $result['status'] = true;
                    break;
                case self::REFUND_STATUS_PENDING:
                    $result['errors'] = ["Refund pending. Need to check. [{$pi_id}]"];
                    break;
                case self::REFUND_STATUS_FAILED:
                    $result['errors'] = ["Refund reason - {$r->failure_reason} [{$pi_id}]"];
                    break;
            endswitch;
        } catch (ApiErrorException $ex) {
            $result['errors'] = [(optional($ex->getError())->message ?? 'Something went wrong') . " [{$pi_id}]"];
            logger()->warning('Stripe refund', ['code' => $ex->getHttpStatus(), 'message' => $ex->getJsonBody()]);
        } catch (\Exception $ex) {
            $result['errors'] = [($ex->getMessage() ?? 'Something went wrong') . " [{$pi_id}]"];
            logger()->warning("Stripe refund", ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
        }
        return $result;
    }

    /**
     * Validates webhook
     * @param  string $sign
     * @param  string $payload
     * @return array
     */
    public function validateWebhook(string $sign, string $payload): array
    {
        $result = ['status' => false];

        $event = null;
        try {
            $event = Webhook::constructEvent($payload, $sign, $this->api->password);
        } catch(SignatureVerificationException $ex) {
            logger()->warning('Stripe wh unauthorized', ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
        } catch(\Exception $ex) {
            logger()->warning('Stripe wh', ['code' => $ex->getMessage(),'message' => $ex->getMessage()]);
        }

        switch (optional($event)->type):
            case 'payment_intent.canceled':
                $pi = $event->data->object; // contains a PaymentIntent
                $result = [
                    'status' => true,
                    'txn' => [
                        'status' => Txn::STATUS_FAILED,
                        'hash'   => $pi->id,
                        'number'  => $pi->metadata['order_number'],
                        'value' => StripeAmountMapper::fromProvider($pi->amount, $pi->currency)
                    ]
                ];
                break;
            case 'payment_intent.succeeded':
                $pi = $event->data->object; // contains a PaymentIntent
                $result = [
                    'status' => true,
                    'txn' => [
                        'status' => Txn::STATUS_APPROVED,
                        'hash'   => $pi->id,
                        'number'  => $pi->metadata['order_number'],
                        'value' => StripeAmountMapper::fromProvider($pi->amount, $pi->currency)
                    ]
                ];
                break;
            default:
                logger()->warning('Stripe unknown event', ['payload' => $payload]);
        endswitch;

        return $result;
    }

}
