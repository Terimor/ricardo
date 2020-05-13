<?php

namespace App\Services;

use App\Constants\PaymentProviders;
use App\Models\Currency;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Models\AffiliateSetting;
use App\Models\OdinProduct;
use App\Models\Localize;

/**
 * Order Service class
 */
class OrderService
{
    /**
     *
     * @param array $data
     * @return type
     */
    public function addTxn(array $data, bool $returnModel = false): array
    {
        // In situation when we need single txn record.
        $model = Txn::firstOrNew(['hash' => $data['hash']]);
        $model->fill($data);

        $validator = $model->validate();
        if ($validator->fails()) {
            logger()->error("Add Txn fails", ['errors' => $validator->errors()->messages()]);
            return [
                'errors' => $validator->errors()->messages(),
                'success' => false
             ];
        } else {
            return [
                'success' => $model->save(),
                'txn' => $returnModel ? $model : $model->attributesToArray(),
                'isNew' => $model->wasRecentlyCreated,
             ];
        }
    }

    /**
     *
     * @param array $data
     * @return type
     */
    public function addOdinOrder(array $data, bool $returnModel = false): array
    {
        $model = new OdinOrder($data);
        if (empty($model->number)) {
            $model->number = !empty($model->shipping_country) ? $model->generateOrderNumber($model->shipping_country) : $model->generateOrderNumber();
        }

        $validator = $model->validate();

        if ($validator->fails()) {
            logger()->error("Add odin order fails", ['errors' => $validator->errors()->messages()]);
            return [
                'errors' => $validator->errors()->messages(),
                'success' => false
            ];
        } else {
            return [
                'success' => $model->save(),
                'order' => $returnModel ? $model : $model->attributesToArray(),
                'affiliate' => isset($affiliate) ? $affiliate : null
            ];
        }
    }

    /**
     * Calculate order total
     * @param type $orderId
     * @return type
     */
    public function calculateOrderAmountTotal($orderId)
    {
        $order = OdinOrder::getById($orderId, false);

        return $this->getOrderProductsText($order);
    }

    /**
     * Checks if the upsells are possible in order
     * @param OdinOrder $order
     * @return bool
     */
    public function checkIfUpsellsPossible(OdinOrder $order): bool
    {
        if (!in_array($order->status, [
            OdinOrder::STATUS_NEW,
            OdinOrder::STATUS_PAID,
            OdinOrder::STATUS_HALFPAID,
        ])) {
            logger()->error('Trying to add product to order with status: ' . $order->status, ['order_id' => $order->getIdAttribute()]);
            return false;
        }
        return true;
    }

    /**
     * Calculates total amount of paid transactions
     * @param OdinOrder $order
     * @return OdinOrder
     */
    public static function calcTotalPaid(OdinOrder $order): OdinOrder
    {
        $currency = CurrencyService::getCurrency($order->currency);

        $total = collect($order->txns)->reduce(function ($carry, $item) {
            if ($item['status'] === Txn::STATUS_APPROVED) {
                $carry += $item['value'];
            }
            return $carry;
        }, 0);

        $order->total_paid      = CurrencyService::roundValueByCurrencyRules($total, $currency->code);
        $order->total_paid_usd  = CurrencyService::roundValueByCurrencyRules($total / $currency->usd_rate, Currency::DEF_CUR);

        return $order;
    }

    /**
     * Get customer data by order ID
     * @param string $orderId
     * @return type
     */
    public static function getCustomerDataByOrderId(string $orderId, $calculateProducts = null)
    {
        $order = OdinOrder::where('_id', $orderId)->select([
            'shipping_country',
            'shipping_zip',
            'shipping_state',
            'shipping_city',
            'shipping_street',
            'shipping_street2',
            'shipping_apt',
            'customer_email',
            'customer_first_name',
            'customer_last_name',
            'customer_phone',
            'customer_doc_id',
            'number',
            'currency',
            'products',
            'txns',
            'type',
            'total_paid_usd',
            'total_refunded_usd'
        ]);

        $order = $order->first();

        // calculate text for products
        if ($calculateProducts && $order) {
            $data = self::getOrderProductsText($order);
            $order->productsText = $data['products'];
            $order->totalText = $data['total_text'];
        }

        return $order;
    }

    /**
     * Returns order type by product
     * @param OdinProduct $product
     * @return string
     */
    public static function getOrderTypeByProduct(OdinProduct $product): string
    {
        return $product->type === OdinProduct::TYPE_VIRTUAL ? OdinOrder::TYPE_VIRTUAL : OdinOrder::TYPE_PHYSICAL;
    }

    /**
     * Check and return reduced order data
     * @param string $orderId
     * @param string $hoAffiliateId
     * @return Localize|null
     * @throws \App\Exceptions\OrderNotFoundException
     */
    public static function getReducedData(string $orderId, string $hoAffiliateId)
    {
        // get order and check is_reduced
        $ol = null;
        $order = OdinOrder::getById($orderId, false);

        if ($order){
            // check if order has the same affiliate and txn status captured, approved
            if ($order->affiliate == $hoAffiliateId && $order->isAcceptedTxn()) {
                // check or create affiliate
                $affiliate = AffiliateSetting::getByHasOfferId($hoAffiliateId);

                 // if not flagged check fired logic
                if ($affiliate && empty($order->is_flagged)) {
                    // get first main product
                    $productId = $order->getFirstProductId();
                    if ($order->is_reduced === null && $productId) {
                        // check in affiliate product list
                        $isReduced = AffiliateSetting::calculateIsReduced($productId, $affiliate, $order->shipping_country);
                        $order->is_reduced = $isReduced;
                        $order->save();
                    }
                    $events = $order->events ?? [];
                    // txid and postback logic
                    if ($order->is_reduced && (!$events || !in_array(OdinOrder::EVENT_AFF_POSTBACK_SENT, $events))) {
                        // request queue if order has parameter txid and is_reduced and aff_id > 10
                        $txid = $order->getParam('txid');
                        $validTxid = AffiliateService::getValidTxid($txid);

                        // save postback
                        AffiliateService::checkAffiliatePostback($hoAffiliateId, $order, $validTxid);
                        $events[] = OdinOrder::EVENT_AFF_POSTBACK_SENT;
                        $order->events = $events;
                        $order->save();
                    }
                }

                $ol = new Localize();
                $ol->is_reduced = $order->is_reduced;
                $ol->is_first_reduced = isset($isReduced) ? true : false;
                $ol->affiliate = !empty($affiliate->ho_affiliate_id) ? $affiliate->ho_affiliate_id : null;
                $ol->is_signup_hidden = isset($affiliate->is_signup_hidden) ? $affiliate->is_signup_hidden : null;
            }
        }
        return $ol;
    }

    /**
     * Get last fail txns percent
     * @return type
     */
    public static function getLastOrdersTxnSuccessPercent($limit = 20, $failPercent = 30): float
    {
        // get last 20 orders with a txns
        $orders = OdinOrder::getLastOrders($limit);
        $success_txns = 0; $successPercent = 0;
        $orderNumbers = [];
        foreach ($orders as $order) {
            $orderTxns = $order->txns;
            foreach ($orderTxns as $txn) {
                if ($txn['status'] ===  Txn::STATUS_APPROVED) {
                    $success_txns++;
                    break;
                }
            }
            $orderNumbers[] = $order->number;
        }

        if ($success_txns > 0) {
            $successPercent = $success_txns / $limit * 100;
        }
        $successPercent = round($successPercent, 0);
        // to log
        if ($successPercent <= $failPercent) {
            logger()->error(str_repeat('*', 30).'ProberTxnsLowPercent'.str_repeat('*', 30), ['success_percent' => $successPercent, 'fail_percent' => $failPercent, 'limit' => $limit, 'numbers' => $orderNumbers]);
        }

        return $successPercent;
    }

    /**
     * Get last affiliate orders firing percent
     * @param type $limit
     * @return float
     */
    public static function getLastOrdersFiringPercent($limit = 100, $failPercent = 30): float
    {
        $orders = OdinOrder::getLastAffiliateOrders($limit);
        $orderNumbers = [];
        $firing = 0;
        foreach ($orders as $order) {
            if ($order->is_reduced === true) {
                $firing++;
            }
            $orderNumbers[] = $order->number;
        }

        $firingPercent = round($firing / $limit * 100, 2);

        // to log
        if ($firingPercent <= $failPercent) {
            logger()->error(str_repeat('*', 30).'ProberLowReducePercent'.str_repeat('*', 30), ['success_percent' => $firingPercent, 'fail_percent' => $failPercent, 'limit' => $limit, 'numbers' => $orderNumbers]);
        }

        return $firingPercent;
    }

    /**
     * Returns txn report as a percentage
     * @param int $orders_limit
     * @return array
     */
    public static function getRecentSuccessTxnReportInPct(int $orders_limit = 100): array
    {
        $report = OdinOrder::getRecentTxnReport(PaymentProviders::getAllActive(), 5, $orders_limit);

        // split report on approved transactions and no
        $grouped = [];
        foreach ($report as $prv => $item) {
            $grouped[$prv] = ['succeed' => 0, 'failed' => 0];
            foreach ($item as $st => $cnt) {
                $grouped[$prv][$st === Txn::STATUS_APPROVED ? 'succeed' : 'failed'] += $cnt;
            }
        }

        $result = [];
        foreach ($grouped as $prv => $item) {
            $result[$prv] = round($item['succeed'] / ($item['succeed'] + $item['failed']) * 100);
        }

        return $result;
    }

    /**
     * Prepate order products text
     * @param type $order
     * @return type
     */
    private static function getOrderProductsText($order)
    {
        // get order currency
        $currency = CurrencyService::getCurrency($order->currency);

        $total = 0;
        // calculate total
        $productsTexts = []; $skuCodes = [];
        foreach ($order->products as $key => $product)
        {
            $productsTexts[$key]['sku_code'] = $product['sku_code'];
            $skuCodes[$product['sku_code']] = $product['sku_code'];

            $total += $product['price'];
            $productsTexts[$key]['price_text'] = CurrencyService::getLocalTextValue($product['price'], $currency);
            // if main add flag and check warranty
            if (isset($product['is_main']) && $product['is_main'])
            {
                $productsTexts[$key]['is_main'] = true;

                // check warranty price
                if (isset($product['warranty_price']) && $product['warranty_price'] > 0 ) {
                    $total += $product['warranty_price'];
                    $productsTexts[$key]['warranty_price_text'] = CurrencyService::getLocalTextValue($product['warranty_price'], $currency);
                }
            } else {
                $productsTexts[$key]['is_main'] = false;
            }
        }

        // get products by skus
        $productsData = OdinProduct::whereIn('skus.code', $skuCodes)->select('_id', 'skus')->pluck('skus', '_id');

        // collect _id to array
        foreach ($order->products as $key => $product) {
            foreach ($productsData as $id => $data) {
                $dataJson = json_encode($data);
                if (strpos($dataJson, $product['sku_code'])) {
                    $productsTexts[$key]['_id'] = $id;
                    break;
                }
            }
        }

        return [
            'products' => $productsTexts,
            'total_text' => CurrencyService::getLocalTextValue($total, $currency)
        ];
    }

}
