<?php

namespace App\Http\Controllers;

use App\Services\ApmService;
use App\Services\BluesnapService;
use App\Services\CardService;
use App\Services\EbanxService;
use App\Services\PaymentService;
use App\Exceptions\AuthException;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\ApmRedirectRequest;
use App\Http\Requests\CreateApmOrderRequest;
use App\Http\Requests\PaymentCardBs3dsRequest;
use App\Http\Requests\PaymentCardMinte3dsRequest;
use App\Http\Requests\CreateApmUpsellsOrderRequest;
use App\Http\Requests\PaymentCardStripe3dsRequest;
use App\Http\Requests\PaymentCardOrderErrorsRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use App\Http\Requests\GetPaymentMethodsByCountryRequest;
use App\Constants\PaymentProviders;
use App\Models\Txn;
use Illuminate\Http\Request;

/**
 * Class PaymentsController
 * @package App\Http\Controllers
 */
class PaymentsController extends Controller
{
    /**
     * Creates card payment
     * @param PaymentCardCreateOrderRequest $req
     * @return array
     * @throws \App\Exceptions\CustomerUpdateException
     * @throws \App\Exceptions\InvalidParamsException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\PaymentException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\ProviderNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function createCardOrder(PaymentCardCreateOrderRequest $req): array
    {
        return CardService::createOrder($req);
    }

    /**
     * Creates APM
     * @param CreateApmOrderRequest $req
     * @return array
     * @throws \App\Exceptions\CustomerUpdateException
     * @throws \App\Exceptions\InvalidParamsException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\PaymentException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\ProviderNotFoundException
     */
    public function createApmOrder(CreateApmOrderRequest $req): array
    {
        return ApmService::createOrder($req);
    }

    /**
     * Creates APM upsells
     * @param CreateApmUpsellsOrderRequest $req
     * @return array
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function createApmUpsellsOrder(CreateApmUpsellsOrderRequest $req): array
    {
        return ApmService::createUpsellsOrder($req);
    }

    /**
     * Creates card upsells payment
     * @param PaymentCardCreateUpsellsOrderRequest $req
     * @return array
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function createCardUpsellsOrder(PaymentCardCreateUpsellsOrderRequest $req)
    {
        return CardService::createUpsellsOrder($req);
    }

    /**
     * Resolves BS 3ds payment
     * @param PaymentCardBs3dsRequest $req
     * @return array
     * @throws \App\Exceptions\InvalidParamsException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function completeBs3dsOrder(PaymentCardBs3dsRequest $req): array
    {
        return CardService::completeBs3dsOrder($req->input('order_id'), $req->input('3ds_ref'));
    }

    /**
     * Returns order errors
     * @param PaymentCardOrderErrorsRequest $req
     * @return array
     * @throws \App\Exceptions\OrderNotFoundException
     */
    public function getCardOrderErrors(PaymentCardOrderErrorsRequest $req)
    {
        $order_id = $req->get('order');
        $reply = PaymentService::getCachedOrderErrors($order_id);
        return ['order_id' => $order_id, 'errors' => $reply ?? []];
    }

    /**
     * Returns payment methods by country
     * @param  GetPaymentMethodsByCountryRequest $req [description]
     * @return array
     */
    public function getPaymentMethodsByCountry(GetPaymentMethodsByCountryRequest $req)
    {
        $country = $req->get('country');
        $currency = $req->get('cur');

        return collect(PaymentService::getPaymentMethodsByCountry($country, $currency))->collapse()->all();
    }

    /**
     * Bluesnap webhook endpoint
     * @param Request $req
     * @return string
     * @throws AuthException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function bluesnapWebhook(Request $req): string
    {
        $authKey = $req->input('authKey', '');
        $type    = $req->input('transactionType');

        $result = "{$authKey}ok";
        if (in_array($type, [BluesnapService::TYPE_WEBHOOK_CHARGE, BluesnapService::TYPE_WEBHOOK_DECLINE])) {
            $reply = BluesnapService::validateWebhook($req);

            if (!$reply['status']) {
                logger()->error('Bluesnap unauthorized webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
                throw new AuthException('Unauthorized');
            }

            $result = "{$authKey}ok";
            if (!empty($reply['txn'])) {
                $order = PaymentService::approveOrder($reply['txn'], PaymentProviders::BLUESNAP);
                $bs = CardService::getBluesnapService($order->number, $reply['txn']['hash']);
                $result .= $bs->getDataProtectionKey();
            }
        } else {
            logger()->info('Bluesnap unprocessed webhook', ['content' => $req->getContent()]);
        }
        return md5($result);
    }

    /**
     * Accepts checkout.com charges.captured webhook
     * @param Request $req
     * @return void
     * @throws AuthException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function checkoutDotComCapturedWebhook(Request $req)
    {
        $order_number = $req->input('data.reference');
        $hash = $req->input('data.id');

        if (!$order_number || !$hash) {
            logger()->error('checkout.com malformed captured webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new \Exception('checkout.com malformed captured webhook');
        }

        $checkout = CardService::getCheckoutService($order_number, $hash);

        $reply = $checkout->validateCapturedWebhook($req);

        if (!$reply['status']) {
            logger()->error('checkout.com unauthorized captured webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('checkout.com captured webhook unauthorized');
        }

        PaymentService::approveOrder($reply['txn'], PaymentProviders::CHECKOUTCOM);
    }

    /**
     * Checkout.com failed webhook
     * @param Request $req
     * @return void
     * @throws AuthException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function checkoutDotComFailedWebhook(Request $req): void
    {
        $order_number = $req->input('data.reference', '');
        $hash = $req->input('data.id');

        if (!$order_number || !$hash) {
            logger()->error('checkout.com malformed failed webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new \Exception('checkout.com malformed failed webhook');
        }

        $checkout = CardService::getCheckoutService($order_number, $hash);

        $reply = $checkout->validateFailedWebhook($req);

        if (!$reply['status']) {
            logger()->error('checkout.com unauthorized failed webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('checkout.com failed webhook unauthorized');
        }

        PaymentService::rejectTxn($reply['txn'], PaymentProviders::CHECKOUTCOM);

        PaymentService::cacheOrderErrors($reply['txn']);
    }

    /**
     * Ebanx notification
     * @param Request $req
     * @return void
     * @throws AuthException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function ebanxWebhook(Request $req)
    {
        $reply = EbanxService::validateWebhook($req);

        if (!$reply['status']) {
            logger()->error('Ebanx unauthorized webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Notification unauthorized');
        }

        CardService::ebanxNotification($reply['hashes']);
    }

    /**
     * Appmax webhook
     * @param Request $req
     * @return void
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function appmaxWebhook(Request $req): void
    {
        $event  = $req->input('event');
        $data   = $req->input('data');

        logger()->info('Appmax webhook debug', ['ip' => $req->ip(), 'body' => $req->getContent()]);

        if (!$event || empty($data)) {
            logger()->error('Appmax malformed webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new \Exception('malformed webhook data');
        }

        CardService::appmaxWebhook($event, $data);
    }

    /**
     * Stripe webhook
     * @param Request $req
     * @return void
     * @throws \Exception
     */
    public function stripeWebhook(Request $req): void
    {
        $sign = $req->header('Stripe-Signature');
        $payload = $req->getContent();

        if (!$sign || !$payload) {
            logger()->error('Stripe malformed wh', ['ip' => $req->ip(), 'sign' => $sign, 'body' => $payload]);
            throw new \Exception('malformed wh data');
        }

        CardService::stripeWebhook($sign, $payload);
    }

    /**
     * Mint-e redirect after 3ds
     * @param PaymentCardMinte3dsRequest $req
     * @param string $order_id
     * @return \Illuminate\Routing\Redirector
     * @throws AuthException
     * @throws \App\Exceptions\InvalidParamsException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function minte3ds(PaymentCardMinte3dsRequest $req, string $order_id)
    {
        $reply = CardService::minte3ds($req, $order_id);

        $query = ['3ds' => $reply['status'] === PaymentService::STATUS_OK ? 'success' : 'failure', 'order' => $order_id];

        if (!empty($reply['bs_pf_token'])) {
            $query['bs_pf_token'] = $reply['bs_pf_token'];
            $query['amount'] = $reply['amount'];
            $query['cur'] = $reply['currency'];
            $query['3ds'] = 'pending';
        } elseif (!empty($reply['redirect_url'])) {
            $query['redirect_url'] = $reply['redirect_url'];
            $query['3ds'] = 'pending';
        }

        return redirect('/checkout?' . http_build_query($query));
    }

    /**
     * Mint-e redirect after APM
     * @param ApmRedirectRequest $req
     * @param string $order_id
     * @return \Illuminate\Routing\Redirector
     * @throws AuthException
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\ProductNotFoundException
     * @throws \App\Exceptions\TxnNotFoundException
     * @throws \Exception
     */
    public function minteApm(ApmRedirectRequest $req, string $order_id)
    {
        $reply = ApmService::minteApm($req, $order_id);

        $status = 'failure';
        if ($reply['status'] === Txn::STATUS_APPROVED) {
            $status = 'success';
            PaymentService::approveOrder($reply, PaymentProviders::MINTE);
        } else {
            logger()->warning('Minte Apm redirect', ['content' => $req->getContent()]);

            $order = PaymentService::rejectTxn($reply, PaymentProviders::MINTE);
            PaymentService::cacheOrderErrors(array_merge($reply, ['number' => $order->number]));
        }

        $path = $reply['is_main'] ? '/checkout' : '/thankyou';

        return redirect("{$path}?order={$order_id}&apm={$status}");
    }

    /**
     * Stripe redirect after 3ds
     * @param PaymentCardStripe3dsRequest $req
     * @param string $order_id
     * @return \Illuminate\Routing\Redirector
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\OrderUpdateException
     * @throws \App\Exceptions\PaymentException
     * @throws \App\Exceptions\TxnNotFoundException
     */
    public function stripe3ds(PaymentCardStripe3dsRequest $req, string $order_id)
    {
        $pi_id = $req->input('payment_intent');

        $reply = CardService::stripe3ds($order_id, $pi_id);

        if (!$reply['status']) {
            logger()->warning('Stripe 3ds redirect', ['ip' => $req->ip(), 'body' => $req->getContent()]);
        }

        $query = ['3ds' => $reply['result'] === PaymentService::STATUS_OK ? 'success' : 'failure', 'order' => $order_id];

        if (!empty($reply['bs_pf_token'])) {
            $query['bs_pf_token'] = $reply['bs_pf_token'];
            $query['amount'] = $reply['amount'];
            $query['cur'] = $reply['currency'];
            $query['3ds'] = 'pending';
        } elseif (!empty($reply['redirect_url'])) {
            $query['redirect_url'] = $reply['redirect_url'];
            $query['3ds'] = 'pending';
        }

        $path = $reply['is_main'] ? '/checkout' : '/thankyou';

        return redirect("{$path}?" . http_build_query($query));
    }

    public function test(Request $req)
    {
        if (\App::environment() === 'production') {
            throw new AuthException('Unauthorized');
        }

        return PaymentService::test($req);
    }

}
