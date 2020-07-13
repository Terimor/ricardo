<?php

namespace App\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use App\Models\{OdinOrder, Domain};
use App\Services\{I18nService, OrderService};
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SupportRequestsController extends Controller
{
    /**
     * Generate code for accessing order and sending to customer email
     * @param Request $request
     * @param \App\Services\EmailService $emailService
     * @param OrderService $orderService
     * @param I18nService $i18nService
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestSupportCode(Request $request, \App\Services\EmailService $emailService, OrderService $orderService, I18nService $i18nService)
    {
        $domain = Domain::getByName();
        $email = mb_strtolower(trim($request->get('email')));
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $orders = OdinOrder::getByEmail($email, ['status']);
        if ($orders->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => $i18nService->getPhraseTranslation('support.order.not_found')
            ]);
        }

        $code = $orderService->generateSupportCode($email);
        $result = $emailService->sendSupportCodeToCustomer($code, $email, $domain->name);

        if (!empty($result['status'])) {
            return response()->json([
                'status' => 1,
                'message' => $i18nService->getPhraseTranslation('support.code.sent')
            ]);
        }

        logger()->error("request support code did not work, {$code} - {$email} - {$domain->name}");
        return response()->json([
            'status' => 500,
            'message' => 'Something went wrong!'
        ]);

    }

    /**
     * Validating support code and email and return orders info for support page
     * @param Request $request
     * @param OrderService $orderService
     * @param I18nService $i18nService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderInfo(Request $request, OrderService $orderService, I18nService $i18nService)
    {
        $i18nService->loadPhrases('support_page');
        $email = mb_strtolower(trim($request->get('email')));
        $code = trim($request->get('code'));
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6']
        ]);

        $orders = $orderService->getOrdersByEmailAndSupportCode($email, $code);
        if (!$orders) {
            return response()->json([
                'status' => 404,
                'message' => t('support.code_is_invalid')
            ]);
        }

        return response()->json([
            'status' => 1,
            'orders' => $orders
        ]);
    }

    /**
     * Handle request of changing order address in support page
     * @param \App\Http\Requests\ChangeOrderAddressRequest $request
     * @param OrderService $orderService
     * @param I18nService $i18nService
     * @param \App\Services\EmailService $emailService
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\OrderNotFoundException
     */
    public function changeOrderAddress(\App\Http\Requests\ChangeOrderAddressRequest $request, OrderService $orderService, I18nService $i18nService, \App\Services\EmailService $emailService)
    {
        try {
            $order = $orderService->getOrderFromSupportRequest($request);
        } catch (\Exception $exception) {
            return response()->json(['status' => 0, 'message' => $exception->getMessage()]);
        }
        $orderData = $orderService->updateShippingAddress($order, $request->all());
        if (!$orderData) {
            return response()->json(['status' => 0, 'message' => 'Something went wrong!']);
        }
        $domain = Domain::getByName();
        $emailResult = $emailService->notifyCustomerAddressChange($order->number, $domain->name);
        if (empty($emailResult['status'])) {
            logger()->error("Address change email sending failed, Order {$order->number},  ".json_encode($emailResult));
        }
        return response()->json([
            'status' => 1,
            'message' => $i18nService->getPhraseTranslation('support.address.changed'),
            'order' => $orderData
        ]);
    }

    /**
     * @param Request $request
     * @param OrderService $orderService
     * @param I18nService $i18nService
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Request $request, OrderService $orderService, I18nService $i18nService)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'number' => ['required']
        ]);
        try {
            $order = $orderService->getOrderFromSupportRequest($request);
        } catch (\Exception $exception) {
            return response()->json(['status' => 0, 'message' => $exception->getMessage()]);
        }
        if ($order->isNotExportedOrder()) {
            return response()->json([
                'status' => 1,
                'message' => 'Order cancelled'
            ]);
        }


    }

}
