<?php

namespace App\Services;

use App\Constants\PaymentProviders;
use App\Models\Currency;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Models\AffiliateSetting;
use App\Models\OdinProduct;
use App\Models\Localize;
use Illuminate\Database\Eloquent\Collection;

/**
 * Order Service class
 */
class OrderService
{

    /**
     * Aftership tracking subtags list
     * Linked to Saga: AftershipAlert::$as_subtags
     * @var array
     */
    public static array $as_subtags = [
        'Delivered_001' => ['Delivered', 'Shipment delivered successfully'],
        'Delivered_002' => ['Picked up by the customer', 'Package picked up by the customer'],
        'Delivered_003' => ['Sign by customer', 'Package delivered to and signed by the customer'],
        'Delivered_004' => ['Delivered and received cash on delivery', 'Package delivered to the customer and cash collected on delivery'],
        'AvailableForPickup_001' => ['Available for pickup', 'The package arrived at a pickup point near you and is available for pickup'],
        'Exception_001' => ['Exception', 'Delivery of the package failed due to some shipping exception'],
        'Exception_002' => ['Customer moved', 'Delivery of the package failed as the customer relocated '],
        'Exception_003' => ['Customer refused delivery', 'Delivery of the package failed as the recipient refused to take the package due to some reason'],
        'Exception_004' => ['Delayed (Customs clearance)', 'Package delayed due to some issues during the customs clearance'],
        'Exception_005' => ['Delayed (External factors)', 'Package delayed due to some unforeseen reasons'],
        'Exception_006' => ['Held for payment', 'The package being held due to pending payment from the customer’s end'],
        'Exception_007' => ['Incorrect Address', 'Package not delivered due to incorrect recipient address'],
        'Exception_008' => ['Pick up missed', 'Package available for the pickup but not collected by the customer'],
        'Exception_009' => ['Rejected by carrier', 'Package rejected by the carrier due to noncompliance with its guidelines'],
        'Exception_010' => ['Returning to sender', 'The package returned to the original sender'],
        'Exception_011' => ['Returned to sender', 'The package returned to the sender'],
        'Exception_012' => ['Shipment damaged', 'Shipment damaged'],
        'Exception_013' => ['Shipment lost', 'Delivery of the package failed as it got lost'],
        'AttemptFail_001' => ['Failed Attempt', 'The delivery of the package failed due to some reason. Courier usually leaves a notice and will try to deliver again'],
        'AttemptFail_002' => ['Addressee not available', 'Recipient not available at the given address'],
        'AttemptFail_003' => ['Business Closed', 'Business is closed at the time of delivery'],
        'InTransit_001' => ['In Transit', 'Shipment on the way'],
        'InTransit_002' => ['Acceptance scan', 'Shipment accepted by the carrier'],
        'InTransit_003' => ['Arrival scan', 'Shipment arrived at a hub or sorting center'],
        'InTransit_004' => ['Arrived at destination country', 'International shipment arrived at the destination country'],
        'InTransit_005' => ['Customs clearance completed', 'Customs clearance completed'],
        'InTransit_006' => ['Customs clearance started', 'Package handed over to customs for clearance'],
        'InTransit_007' => ['Departure Scan', 'Package departed from the facility'],
        'InTransit_008' => ['Problem resolved', 'Problem resolved and shipment in transitt'],
        'InTransit_009' => ['Forwarded to a different delivery address', 'Shipment forwarded to a different delivery address'],
        'InfoReceived_001' => ['Info Received', 'The carrier received a request from the shipper and is about to pick up the shipment'],
        'OutForDelivery_001' => ['Out for Delivery', 'The package is out for delivery'],
        'OutForDelivery_002' => ['Available for pickup', 'The package has arrived at sorting center and is available for pickup'],
        'OutForDelivery_003' => ['Customer contacted', 'The customer is contacted before the final delivery'],
        'OutForDelivery_004' => ['Delivery appointment scheduled', 'A delivery appointment is scheduled'],
        'Pending_001' => ['Pending', 'No information available on the carrier website or the tracking number is yet to be tracked'],
        'Expired_001' => ['Expired', 'No tracking information of the shipment, from last 30 days']
    ];

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
        $total = collect($order->txns)->reduce(function ($carry, $item) {
            if ($item['status'] === Txn::STATUS_APPROVED) {
                $carry += $item['value'];
            }
            return $carry;
        }, 0);

        $order->total_paid = CurrencyService::roundValueByCurrencyRules($total, $order->currency);
        $order->total_paid_usd = CurrencyService::roundValueByCurrencyRules($total / $order->exchange_rate);

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
                    // allow reducing only if is_reduced is null
                    if ($order->is_reduced === null && $productId) {
                        // if affiliate has access to product
                        $product = OdinProduct::getById($productId, ['reducings', 'reduce_percent', 'initial_reduce_percent', 'is_public']);
                        if ($affiliate->hasProductAccess($product)) {
                            // check in affiliate product list
                            $isReduced = AffiliateSetting::calculateIsReduced($product, $affiliate, $order->shipping_country);
                            $order->is_reduced = $isReduced;
                            $order->save();
                        }
                    }
                    $events = $order->events ?? [];
                    // txid and postback logic
                    if ($order->is_reduced && (!$events || !in_array(OdinOrder::EVENT_AFF_POSTBACK_SENT, $events))) {
                        // request queue if order has parameter txid and is_reduced and aff_id > 10
                        $txid = $order->getParam('txid');
                        $validTxid = AffiliateService::getValidTxid($txid);

                        // save postback
                        AffiliateService::checkAffiliatePostback($hoAffiliateId, $order, $validTxid);
                        $order->addEvent(OdinOrder::EVENT_AFF_POSTBACK_SENT, true);
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

    /**
     * Return cache key for support code functionality by email
     * @param string $email
     * @return string
     */
    protected function getSupportCodeCacheKey(string $email)
    {
        $emailHash = hash('crc32', $email);
        return 'OrderCode'.$emailHash;
    }

    /**
     * Generate support code for the accessing orders and save in cache
     * @param string $email
     * @return string
     */
    public function generateSupportCode(string $email): string
    {
        $code = strval(rand(100000, 999999));
        \Cache::put($this->getSupportCodeCacheKey($email), $code, 12 * 3600);
        return $code;
    }

    /**
     * Return array of sku names from orders products
     * @param Collection $orders
     * @return array
     */
    public function getSkusNamesFromOrders(Collection $orders): array
    {
        $skus = [];
        foreach ($orders as $order) {
            /* @var  OdinOrder $order*/
            foreach ($order->products as $product) {
                $skus[$product['sku_code']] = $product['sku_code'];
            }
        }

        $skusData = OdinProduct::getSkusArrayByCodes(array_keys($skus), ['skus.code', 'skus.name']);
        foreach ($skusData as $skuData) {
            foreach ($skuData as $skuItem) {
                $skus[$skuItem['code']] = $skuItem['name'];
            }
        }
        return $skus;
    }

    /**
     * Validate support code
     * @param string $email
     * @param string $code
     * @return bool
     */
    public function validateSupportCode(string $email, string $code): bool
    {
        $cachedCode = \Cache::get($this->getSupportCodeCacheKey($email));
        return $cachedCode && $cachedCode === $code;
    }


    public function getSkuNamesFromOrder(OdinOrder $order): array
    {
        $skus = [];
        foreach ($order->products as $product) {
            $skus[$product['sku_code']] = $product['sku_code'];
        }
        $skusData = OdinProduct::getSkusArrayByCodes(array_keys($skus), ['skus.code', 'skus.name']);
        foreach ($skusData as $skuData) {
            foreach ($skuData as $skuItem) {
                $skus[$skuItem['code']] = $skuItem['name'];
            }
        }
        return $skus;
    }

    /**
     * Returns array containing order fields prepared for support page
     * @param OdinOrder $order
     * @param array|null $skus
     * @return array
     */
    public function prepareOrderDataForSupportPage(OdinOrder $order, ?array $skus = []): array
    {
        if (!$skus) {
            $skus = $this->getSkuNamesFromOrder($order);
        }
        $orderData = $order->toArray();
        $returnFields = array_merge($this->getSupportPageRelatedFieldsNames(), ['countries', 'allowEditAddress', 'shipping_country_name']);
        foreach ($orderData as $field => $value) {
            if (!in_array($field, $returnFields)) {
                unset($orderData[$field]);
            }
        }
        $orderData['shipping_country_name'] = t('country.'.$orderData['shipping_country']);
        $orderData['created_at'] = $order->created_at->toDatetime()->format(config('app.date_format'));
        $orderData['trackings'] = $this->prepareTrackingsData($orderData['trackings'] ?? []);
        $currency = CurrencyService::getCurrency($order->currency);
        $orderData['total_paid'] = CurrencyService::getLocalTextValue($orderData['total_paid'], $currency);
        $orderData['products'] = $this->prepareProductsData($order->products, $currency, $skus);
        $orderData['countries'] = [];
        $mainProduct = $order->getMainProduct(false);
        if ($mainProduct) {
            $product = OdinProduct::getBySku($mainProduct['sku_code'], false, [
                'is_europe_only', 'countries', 'skus', 'type'
            ]);
            if ($product) {
                $orderData['countries'] = UtilsService::getShippingCountries(true, $product);
            }
        }
        $orderData['allowEditAddress'] = $order->isAllowedEditAddress();
        return $orderData;
    }

    /**
     * Returns orders data by given code and email
     * @param string $email
     * @param string $code
     * @return array
     */
    public function getOrdersByEmailAndSupportCode(string $email, string $code): array
    {
        $result = [];
        if (!$this->validateSupportCode($email, $code)) {
            return $result;
        }
        $orders = OdinOrder::getByEmail($email, $this->getSupportPageRelatedFieldsNames());
        if ($orders) {
            $skus = $this->getSkusNamesFromOrders($orders);
            foreach ($orders as $order) {
                /* @var OdinOrder $order*/
                $result[] = $this->prepareOrderDataForSupportPage($order, $skus);
            }
            return $result;
        }
    }

    /**
     * set trackings status and dates formated values
     * @param array $trackings
     * @return array
     */
    public function prepareTrackingsData(array $trackings)
    {
        foreach ($trackings as &$tracking) {
            $tracking['added_at'] = $tracking['added_at']->toDatetime()->format(config('app.datetime_format'));
            $tracking['status_at'] = !empty($tracking['status_at']) ? $tracking['status_at']->toDatetime()->format(config('app.datetime_format')) : '';
            if (!empty($tracking['status'])) {
                $tracking['status'] = static::$as_subtags[$tracking['status']][1] ?? $tracking['status'];
            }
        }
        return $trackings;
    }

    /**
     * Set products names by sku codes
     * @param array $products
     * @param Currency $currency
     * @param array $skus
     * @return array
     */
    public function prepareProductsData(array $products, Currency $currency, array $skus): array
    {
        foreach ($products as &$product) {
            $product['name'] = $skus[$product['sku_code']] ?? $product['sku_code'];
            $product['price'] = $product['is_paid'] ?
                CurrencyService::getLocalTextValue($product['price'], $currency) :
                t('support.not_paid');
        }
        return $products;
    }

    /**
     * Returns array of with mapping fields of model and request
     * @return array
     */
    public function getShippingFieldsMapping(): array
    {
        return  [
            'shipping_country' => 'country',
            'shipping_zip' => 'zipcode',
            'shipping_state' => 'state',
            'shipping_city' => 'city',
            'shipping_street' => 'street',
            'shipping_street2' => 'district',
            'shipping_building' => 'building',
            'shipping_apt' => 'complement'
        ];
    }

    /**
     * Update order shipping address and return order info data array for support page
     * @param OdinOrder $order
     * @param array $addressFields
     * @return array|null
     */
    public function updateShippingAddress(OdinOrder $order, array $addressFields): ?array
    {
        $order->updateReason = \App\models\DataHistory::REASON_CUSTOMER;
        if (!$order->update($addressFields)) {
            return null;
        }
        return $this->prepareOrderDataForSupportPage($order);
    }

    public function getSupportPageRelatedFieldsNames(): array
    {
        return [
            'number', 'status', 'is_paused', 'shipping_country', 'created_at', 'trackings', 'total_paid', 'products',
            'shipping_apt', 'shipping_country', 'shipping_zip', 'shipping_state', 'shipping_city', 'shipping_street',
            'shipping_street2', 'shipping_building', 'shipping_apt',
            'customer_first_name', 'customer_last_name', 'customer_phone', 'customer_email'
        ];
    }

}
