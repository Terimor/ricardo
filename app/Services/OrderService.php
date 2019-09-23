<?php

namespace App\Services;
use App\Models\Setting;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Models\OdinCustomer;
use App\Services\EmailService;
use App\Models\OdinProduct;
use Illuminate\Support\Arr;

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
                'order' => $returnModel ? $model : $model->attributesToArray()
             ];
        }
    }

    /**
     * Add customer
     * @param array $data
     * @return array
     */
    public function addCustomer(array $data, bool $returnModel = false): array
    {
        $model = OdinCustomer::firstOrNew(['email' => $data['email']]);
        $model->fill($data);

        // add ip if not in array
        $ip = !empty($data['ip']) ? $data['ip'] : request()->ip();
        if (!in_array($ip, $model->ip)) {
            $model->ip = array_merge($model->ip, [$ip]);
        }

        // add phone if not in array
        if (!empty($data['phone']) && !in_array($data['phone'], $model->phones)) {
            $model->phones = array_merge($model->phones, [$data['phone']]);
        }

		// doc_ids
		if (!empty($data['doc_id']) && !in_array($data['doc_id'], $model->doc_ids)) {
            $model->doc_ids = array_merge($model->doc_ids, [$data['doc_id']]);
        }

        // addresses
        $address = [
            'country' => !empty($data['country']) ? trim($data['country']) : '',
            'zip' => !empty($data['zip']) ? trim($data['zip']) : '',
            'state' => !empty($data['state']) ? trim($data['state']) : '',
            'city' => !empty($data['city']) ? trim($data['city']) : '',
            'street' => !empty($data['street']) ? trim($data['street']) : '',
            'street2' => !empty($data['street2']) ? trim($data['street2']) : '',
        ];

        // json coding for compare strings
        $addressJson = json_encode($address);
        $modelAddressesJson = json_encode($model->addresses);

        // add address if not in array
        if (!strstr(' '.$modelAddressesJson, $addressJson)) {
            $model->addresses = array_merge($model->addresses, [$address]);
        }

        $validator = $model->validate();

        if ($validator->fails()) {
            logger()->error("Add odin customer fails", ['errors' => $validator->errors()->messages()]);
            return [
                'errors' => $validator->errors()->messages(),
                'success' => false
             ];
        } else {
            return [
                'success' => $model->save(),
                'customer' => $returnModel ? $model : $model->attributesToArray()
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
            'currency'
		]);
        
        // select products array
        if ($calculateProducts) {            
            $order->addSelect('products', 'currency');
        }
        
        $order = $order->first();
        
        // calculate text for products
        if ($calculateProducts) {
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
        $order = OdinOrder::where('_id', $orderId)->first();

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
}
