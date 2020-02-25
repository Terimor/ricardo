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
use App\Http\Requests\PaymentCardOrderErrorsRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use App\Http\Requests\GetPaymentMethodsByCountryRequest;
use App\Constants\PaymentProviders;
use App\Models\Txn;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    /**
     * Creates card payment
     * @param  PaymentCardCreateOrderRequest $req
     * @return array
     */
    public function createCardOrder(PaymentCardCreateOrderRequest $req)
    {
        return CardService::createOrder($req);
    }

    /**
     * Creates APM
     * @param  CreateApmOrderRequest $req
     * @return array
     */
    public function createApmOrder(CreateApmOrderRequest $req)
    {
        return ApmService::createOrder($req);
    }

    /**
     * Creates APM upsells
     * @param  CreateApmOrderRequest $req
     * @return array
     */
    public function createApmUpsellsOrder(CreateApmUpsellsOrderRequest $req)
    {
        return ApmService::createUpsellsOrder($req);
    }

    /**
     * Creates card upsells payment
     * @param  PaymentCardCreateUpsellsOrderRequest $req
     * @return array
     */
    public function createCardUpsellsOrder(PaymentCardCreateUpsellsOrderRequest $req)
    {
        return CardService::createUpsellsOrder($req);
    }

    /**
     * Resolves BS 3ds payment
     * @param  PaymentCardBs3dsRequest $req
     * @return array
     */
    public function completeBs3dsOrder(PaymentCardBs3dsRequest $req)
    {
        return CardService::completeBs3dsOrder($req->input('order_id'), $req->input('3ds_ref'));
    }

    /**
     * Returns order errors
     * @param  PaymentCardOrderErrorsRequest $req
     * @return array
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
        return collect(PaymentService::getPaymentMethodsByCountry($req->get('country')))->collapse()->all();
    }

    /**
     * Bluesnap webhook endpoint
     * @param  Request $req
     * @return string
     */
    public function bluesnapWebhook(Request $req): string
    {
        $authKey = $req->input('authKey', '');
        $type    = $req->input('transactionType');

        if (!in_array($type, [BluesnapService::TYPE_WEBHOOK_CHARGE, BluesnapService::TYPE_WEBHOOK_DECLINE])) {
            logger()->info('Bluesnap unprocessed webhook', ['content' => $req->getContent()]);
        }

        $reply = BluesnapService::validateWebhook($req);

        if (!$reply['status']) {
            logger()->error('Bluesnap unauthorized webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $order = PaymentService::approveOrder($reply['txn'], PaymentProviders::BLUESNAP);

        $bs = CardService::getBluesnapService($order->number, $reply['txn']['hash']);

        return md5($authKey . 'ok' . $bs->getDataProtectionKey());
    }

    /**
     * Accepts checkout.com charges.captured webhook
     * @param  Request $req
     * @return void
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
     * @param  Request $req
     * @return void
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
     * @param  Request $req
     * @return void
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
     * @param  Request $req
     * @return void
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
     * Mint-e redirect after 3ds
     * @param PaymentCardMinte3dsRequest $req
     * @param string $order_id
     * @return void
     */
    public function minte3ds(PaymentCardMinte3dsRequest $req, string $order_id)
    {
        $reply = CardService::minte3ds($req, $order_id);

        $query = array_filter([
            '3ds'   => $reply['status'] === PaymentService::STATUS_OK ? 'success' : 'failure',
            'order' => $order_id,
            'bs_pf_token'   => $reply['bs_pf_token'] ?? null,
            'redirect_url'  => $reply['redirect_url'] ?? null
        ]);

        if (!empty($query['bs_pf_token']) || !empty($query['redirect_url'])) {
            $query['amount'] = $reply['amount'];
            $query['cur'] = $reply['currency'];
            $query['3ds'] = 'pending';
        }

        return redirect('/checkout?' . http_build_query($query));
    }

    /**
     * Mint-e redirect after APM
     * @param  ApmRedirectRequest $req
     * @param  string             $order_id
     * @return type
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

    public function test(Request $req)
    {
        if (\App::environment() === 'production') {
            throw new AuthException('Unauthorized');
        }

        return PaymentService::test($req);
    }

}
