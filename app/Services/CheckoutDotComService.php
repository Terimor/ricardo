<?php

namespace App\Services;

use App\Models\OdinOrder;
use App\Models\Setting;
use App\Models\Txn;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Checkout\CheckoutApi;
use Checkout\Models\Payments\Payment;
use Checkout\Models\Payments\CardSource;
use Checkout\Library\Exceptions\CheckoutHttpException;
use Checkout\Library\Exceptions\CheckoutException;
/**
 * CheckoutDotComService class
 */
class CheckoutDotComService
{
    const ENV_LIVE = 'live';
    const ENV_SANDBOX = 'sandbox';

    const SUCCESS_CODE = 10000;
    const SUCCESS_FLAGGED_CODE = 10100;

    const TYPE_WEBHOOK_CAPTURED = 'payment_captured';

    /**
     * @var string
     */
    private $secret_key;

    /**
     * @var string
     */
    private $captured_webhook_secret_key;

    /**
     * @var string
     */
    private $public_key;

    /**
     * @var CheckoutApi
     */
    private $checkout;

    /**
     * CheckoutDotController constructor.
     */
    public function __construct()
    {
        $this->secret_key = Setting::getValue('checkout_dot_com_secret_key');

        if (!$this->secret_key) {
            logger()->error("Checkout.com secret_key not found");
        }

        $this->public_key = Setting::getValue('checkout_dot_com_public_key');

        if (!$this->public_key) {
            logger()->error("Checkout.com public_key not found");
        }

        $this->captured_webhook_secret_key = Setting::getValue('checkout_dot_com_captured_webhook_secret_key');

        if (!$this->captured_webhook_secret_key) {
            logger()->error("Checkout.com captured_webhook_secret_key not found");
        }

        $env = Setting::getValue('checkout_dot_com_api_env', self::ENV_LIVE);

        $this->checkout = new CheckoutApi($this->secret_key, $env === self::ENV_SANDBOX);
    }

    /**
     * Creates a new payment
     * @param array $card
     * @param array $contact
     * @param OdinOrder $order
     * @return array
     */
    public function pay(array $card, array $contact, OdinOrder $order)
    {
        $source = new CardSource($card['number'], $card['month'], $card['year']);
        $source->cvv = $card['cvv'];
        $source->name = $contact['first_name'] . ' ' . $contact['last_name'];
        $source->phone = (object)[$contact['phone']];
        $source->billing_address = (object)[
            'address_line1' => $contact['street'],
            'city' => $contact['city'],
            'country' => $contact['country'],
            'state' => $contact['state'],
            'zip' => $contact['zip']
        ];

        $payment = new Payment($source, $order->currency);
        $payment->reference = $order->number;
        $payment->amount = $order->total_price;
        $payment->description = 'Product Description';
        $payment->customer = (object)['email' => $contact['email'], 'name' => $source->name];
        $payment->shipping = (object)['address' => $source->billing_address, 'phone' => $source->phone];
        $payment->payment_ip = $order->ip;

        $result = [
            'is_flagged' => false,
            'hash' => null,
            'currency' => $order->currency,
            'value' => $order->number,
            'status' => Txn::STATUS_FAILED,
            'fee' => 0,
            'provider_data' => null,
            'payment_provider' => PaymentService::PROVIDER_CHECKOUTCOM,
            'payment_method' => $card['type'],
            'payer_id' => null
        ];

        // parse response
        try {
            $res = $this->checkout->payments()->request($payment);

            $result['provider_data'] = $res;
            $result['currency'] = $res->currency;
            $result['hash'] = $res->id;
            $result['value'] = $res->amount;
            $result['payer_id'] = $res->customer['id'];

            $res_code = (int)$res->response_code;
            if ($res_code === self::SUCCESS_CODE || $res_code === self::SUCCESS_FLAGGED_CODE) {
                $result['status'] = Txn::STATUS_CAPTURED;
            }
            $result['is_flagged'] = $res_code === self::SUCCESS_FLAGGED_CODE;
        } catch (CheckoutHttpException $ex) {
            $body = json_decode($ex->getBody(), true);
            if (!empty($body['request_id'])) {
                $result['hash'] = $body['request_id'];
            }
            $result['provider_data'] = $body;
            logger()->error("Checkout.com HTTP", ['code' => $ex->getCode(), 'errors' => $ex->getErrors()]);
        } catch (CheckoutException $ex) {
            $result['provider_data'] = ['code' => $ex->getCode(), 'errors' => $ex->getErrors()];
            logger()->error("Checkout.com", ['code' => $ex->getCode(), 'errors' => $ex->getErrors()]);
        }

        return $result;
    }

    /**
     * Validates webhook
     * @param  Request $req
     * @return array
     */
    public function validateCaptureWebhook(Request $req)
    {
        $auth_secret = $req->header('authorization');
        $auth_sign = $req->header('cko-signature');
        $content = $req->getContent();
        $type = $req->get('type');
        $data = $req->get('data') ?? [];

        $result = [ 'status' => false ];

        if ($type === self::TYPE_WEBHOOK_CAPTURED && $auth_secret === $this->captured_webhook_secret_key) {
            $sign = hash_hmac('sha256', $content, $this->secret_key);
            if ($auth_sign === $sign && !empty($data)) {
                $result = [
                    'status'    => true,
                    'hash'      => $data['id'],
                    'number'    => $data['reference'],
                    'value'     => $data['amount'],
                    'currency'  => $data['currency']
                ];
            }
        }

        return $result;
    }

}
