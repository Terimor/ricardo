<?php

namespace App\Services;
use App\Services\CurrencyService;
use App\Models\OdinProduct;
use App\Models\OdinCustomer;
use App\Models\OdinOrder;
use App\Models\Txn;
use App\Services\OrderService;
use App\Models\Setting;
/**
 * Ebanx Service class
 */
class EbanxService
{

    /**
     * EbanxController constructor.
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->currency = CurrencyService::getCurrency();


	$this->key = Setting::where(['key' => 'ebanx_integration_key'])->first();

        if (!$this->key) {
            logger()->error("ebanx_integration_key parameter not found");
        }
    }

    /**
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $mode = Setting::where(['key' => 'ebanx_mode'])->first();

        if (!$mode) {
            logger()->error("ebanx_mode parameter not found");
        }

        if ($mode && $mode->value == 'prod') {
            $url = 'https://sandbox.ebanxpay.com/';
        } else {
            $url = 'https://sandbox.ebanxpay.com/';
        }
        return $url;
    }

    /**
     * Save customer
     * @param array $request
     * @param type $returnModel
     * @return array
     */
    public function saveCustomer(array $request): OdinCustomer
    {
        $data = [
            'email' => $request['email'],
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'ip' => request()->ip(),
            'phone' => $request['phone'],
            'language' => app()->getLocale(),
            'country' => $request['country'],
            'zip' => $request['zipcode'],
            'state' => $request['state'],
            'city' => $request['city'],
            'street' => $request['address'],
            'street2' => $request['street_number'],
	    'doc_id' => !empty($request['document']) ? $request['document'] : null
        ];

        $res = $this->orderService->addCustomer($data, true);
        if ($res['success']) {
            return $res['customer'];
        } else {
            abort(404);
        }
    }

    /**
     * Save order
     * @param array $request
     * @param OdinCustomer $customer
     * @param OdinProduct $product
     */
    public function saveOrder(array $request, OdinCustomer $customer, OdinProduct $product)
    {
        $price = (float)$product->prices[$request['quantity']]['value'];

        $warrantyPrice = !empty($request['is_warranty_checked']) ? (float)$product->prices[$request['quantity']]['warranty_price'] : 0;

        $productForOrder = [
            "sku_code" => $request['sku'],
            "quantity" => (int)$request['quantity'],
            "price" => $price,
            "price_usd" => round($price / (!empty($this->currency->price_rate) ? $this->currency->price_rate : $this->currency->usd_rate), 2),
            "warranty_price" => $warrantyPrice,
            "warranty_price_usd" => floor($warrantyPrice / (!empty($this->currency->price_rate) ? $this->currency->price_rate : $this->currency->usd_rate) * 100)/100,
            'price_set' => $product->prices['price_set'],
	    'is_main' => isset($request['is_main']) ? $request['is_main'] : true,
        ];

        // installments
        if(!empty($request['installments']) && ($request['installments'] == 3 || $request['installments'] == 6)) {
            $installments = $request['installments'];
        } else {
            $installments = 0;
        }

        $data = [
            'status' => OdinOrder::STATUS_NEW,
            'currency' => $this->currency->code,
            'exchange_rate' => $this->currency->usd_rate, // * float
            'total_paid' => $productForOrder['price'],
            'total_price' => $productForOrder['price'] + $productForOrder['warranty_price'],
            'total_price_usd' => floor($productForOrder['price'] + $productForOrder['warranty_price'] / (!empty($this->currency->price_rate) ? $this->currency->price_rate : $this->currency->usd_rate) * 100) / 100,
            //'txns_fee_usd' => null, //float, total amount of all txns' fee in USD
            'installments' => $installments,
            //'payment_provider' => 'ebanx',
            //'payment_method' => $request['payment_type_code'],
            'customer_email' => $request['email'],
            'customer_first_name' => $request['first_name'],
            'customer_last_name' => $request['last_name'],
            'customer_phone' => $request['phone'],
            'language' => app()->getLocale(),
            'ip' => request()->ip(),
            'shipping_country' => $request['country'],
            'shipping_zip' => $request['zipcode'],
            'shipping_state' => $request['state'],
            'shipping_city' => $request['city'],
            'shipping_street' => $request['address'],
            'shipping_street2' => $request['street_number'],
            'warehouse_id' => $product->warehouse_id,
            'products' => [$productForOrder],
        ];

        $res = $this->orderService->addOdinOrder($data, true);

        if ($res['success']) {
            return $res['order'];
        } else {
            abort(404);
        }
    }

    /**
     * Prepare data for curl
     * @param array $data
     * @return array
     */
    public function prepareDataCurl(array $data, string $orderNumber): array
    {
        // installments
        if(!empty($data['installments']) && ($data['installments'] == 3 || $data['installments'] == 6)) {
            $installments = $data['installments'];
        } else {
            $installments = 1;
        }

        $dataForCurl = [
            "integration_key" => $this->key->value,
            "operation" => "request",
            "mode" => "full",
            "payment" => [
                "amount_total" => $data['amount_total'],
                "currency_code" => $this->currency->code,
                "name" => $data['first_name'].' '.$data['last_name'],
                "merchant_payment_code" => \Utils::randomString(10),
                "email" => $data['email'],
                "birth_date" => $data['birth_date'],
                "document" => $data['document'],
                "address" => $data['address'],
                "street_number" => $data['street_number'],
                "city" => $data['city'],
                "state" => $data['state'],
                "zipcode" => $data['zipcode'],
                "country" => $data['country'],
                "phone_number" => $data['phone'],
                "payment_type_code" => $data['payment_type_code'],
                "instalments" => $installments,
                "creditcard" => [
                    "token" => $data['token']
                ],
                'order_number' => $orderNumber,
            ]
        ];

        return $dataForCurl;
    }

    /**
     *
     * @param array $response
     * @return type
     */
    public function saveTxn(array $response)
    {
        $data = [
            'hash' => !empty($response['payment']['hash']) ? $response['payment']['hash'] : null,
            'value' => !empty($response['payment']['amount_br']) ? $response['payment']['amount_br'] : null,
            'currency' => $this->currency->code,
            'provider_data' => $response,
            'payment_provider' => 'ebanx',	   
            'payment_method' => !empty($response['payment']['payment_type_code']) ? $response['payment']['payment_type_code'] : null,
        ];	

        $res = $this->orderService->addTxn($data, true);

        if ($res['success']) {
            return $res['txn'];
        } else {
            abort(404);
        }
    }

    /**
     * Send transaction
     * @param array $dataForCurl
     * @return string
     */
    public function sendTransaction(array $dataForCurl) : string
    {
        $url = $this->getBaseUrl().'ws/direct';

        try {
            $client = new \GuzzleHttp\Client();
            $request = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                \GuzzleHttp\RequestOptions::JSON => $dataForCurl
            ]);
            $response = $request->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }

        return $response;
    }

    /**
     * Save txn and recalculate txns_fee_usd
     * @param OdinOrder $order
     * @param \App\Services\Txb $txn
     * @param string $sku
     */
    public function saveTxnResponseForOrder(OdinOrder $order, Txn $txn, $response)
    {
        $txnsFeeUsd = 0;
	$txns = [];
        if ($order->txns) {
	    $txns = $order->txns ;
	}
	$dataTxnArray = [
	    'hash' => $txn->hash,
	    'value' => $txn->value,
	    'status' => 'new',
	    'is_charged_back' => false,
	    'payment_provider' => 'ebanx',
	    'payment_method' => !empty($response['payment']['payment_type_code']) ? $response['payment']['payment_type_code'] : null,
	];

	// check status transaction, if CO=paid
	if (!empty($response['payment']['status'])) {
	    if ($response['payment']['status'] == 'CO') {
		$dataTxnArray['status'] = 'approved';
	    }
	}

	if (!empty($response['payment']['amount_iof'])) {
	    $dataTxnArray['fee'] = (float)$response['payment']['amount_iof'];
	}

	$txns[] = $dataTxnArray;

	// re	calculate txns fee
	foreach ($txns as $t) {
	    if (!empty($t['value'])) {
		$txnsFeeUsd += round($t['value'] / $this->currency->usd_rate, 5);
	    }
	}
	
	$order->txns = $txns;
        $order->txns_fee_usd = (float)$txnsFeeUsd;
        $order->save();
    }

    /**
     *
     * @param type $hashCodes
     */
    public function updateProductStatuses($hashCodes)
    {
	foreach ($hashCodes as $hash) {
	    //find order product by hash
	    $order = OdinOrder::where(['products.txn_hash' => $hash])->first();

	    $res = json_decode($this->sendQueryHash($hash), true);
	    if(!empty($res['payment']['status'])) {
		$status = $res['payment']['status'];
		$products = $order->products;
		foreach ($products as &$p) {
		    if($p['txn_hash'] == $hash) {
			if ($status == 'CO') {
			    $p['is_txn_approved'] = true;
			}

			if ($status == 'CA') {
			    $p['is_txn_approved'] = false;
			}

			$order->products = $products;
			$order->save();
			break;
		    }
		}
	    }
	}
    }


    public function sendQueryHash(string $hash) : string
    {
	$url = $this->getBaseUrl()."ws/query";

	$dataForCurl = [
	    'hash' => $hash,
	    'integration_key' => $this->key->value
	];

        try {
            $client = new \GuzzleHttp\Client();
            $request = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                \GuzzleHttp\RequestOptions::JSON => $dataForCurl
            ]);
            $response = $request->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }

        return $response;
    }
}
