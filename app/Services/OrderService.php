<?php

namespace App\Services;
use App\Models\Setting;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Models\AffiliateSetting;
use App\Models\RequestQueue;
use App\Services\EmailService;
use App\Models\OdinProduct;
use Illuminate\Support\Arr;
use App\Models\Localize;
use App\Services\AffiliateService;

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
            'txns'
		]);

        // select products array
        // if ($calculateProducts) {
        //     $order->addSelect('products', 'currency');
        // }

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
     * Check and return reduced order data
     * @param type $orderId
     * @param type $affiliate
     */
    public static function getReducedData(string $orderId, string $hoAffiliateId)
    {
        // get order and check is_reduced
        $ol = null;
        $order = OdinOrder::getById($orderId, false);

        if ($order){
            // check if order has the same affiliate and txn status captured, approved
            if ($order->affiliate == $hoAffiliateId && $order->isTxnForReduce()) {
                // check or create affiliate
                $affiliate = AffiliateSetting::firstOrCreate(['ho_affiliate_id' => $hoAffiliateId]);

                 // if not flagged check fired logic
                if (empty($order->is_flagged)) {
                    // get first main product                    
                    $productId = $order->getFirstProductId();
                    if ($order->is_reduced === null && $productId) {
                        // check in affiliate product list
                        $isReduced = AffiliateSetting::calculateIsReduced($productId, $affiliate);
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
     * Get last fail txns percent
     * @return type
     */
    public static function getLastOrdersTxnSuccessPercent($limit = 20)
    {
        // get last 20 orders with a txns        
        $orders = OdinOrder::getLastOrders($limit);        
        $success_txns = 0; $successPercent = 0;
        foreach ($orders as $order) {            
            $orderTxns = $order->txns;            
            foreach ($orderTxns as $txn) {
                if ($txn['status'] ===  Txn::STATUS_APPROVED) {
                    $success_txns++;
                    break;
                }
            }           
        }

        if ($success_txns > 0) {
            $successPercent = $success_txns / $limit * 100;
        }
        $successPercent = round($successPercent, 0);

        return $successPercent;
    }
}
