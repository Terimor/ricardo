<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Http\Requests\EbanxSendTransactionRequest;
use App\Services\EbanxService;
use App\Models\OdinProduct;

class EbanxController extends Controller
{
    
    /**
     * @var EbanxService
     */
    protected $ebanxService;
    
    /**
     * EbanxController constructor.
     * @param OrderService $orderService
     */
    public function __construct(EbanxService $ebanxService)
    {
        $this->ebanxService = $ebanxService;
    }
    /**
     * Send transaction
     * @return type
     */
    public function sendTransaction(EbanxSendTransactionRequest $request) 
    {
        //check product       
        $product = OdinProduct::where('skus.code', $request->input('sku'))->first();
        if (!$product) {
            logger()->error("Ebanx send transaction: SKU not found", ['sku' => $request->input('sku')]);
            return response()->json(['error' => ['SKU not found']], 402);
        }        
        
        //check prices
        $priceQty = !empty($product->prices[$request->input('quantity')]) ? $product->prices[$request->input('quantity')] : null;        
        if ($priceQty['value'] != $request->input('amount_total')) {            
            logger()->error("Ebanx send transaction: Prices do not match", ['priceQty' => $priceQty, 'amount_total' => $request->input('amount_total')]);
            return response()->json(['error' => ['Prices do not match']], 402);
        }                
        
        // customer 
        $customer = $this->ebanxService->saveCustomer($request->all());
        
        // save order
        $order = $this->ebanxService->saveOrder($request->all(), $customer, $product);

        // set data for curl
        $dataForCurl = $this->ebanxService->prepareDataCurl($request->all(), $order->number);

        // send transaction
        $response = $this->ebanxService->sendTransaction($dataForCurl);        
        $response = json_decode($response, true);
        
        // save txn

        if ($response['status'] === "SUCCESS") {
            $txn = $this->ebanxService->saveTxn($response);
            
            //update order product
            $this->ebanxService->saveTxnForOrderProduct($order, $txn, $request->input('sku'));
            $result = ['status' => "SUCCESS"];
        } else {
            $result = ['status' => "ERROR", 'message' => !empty($response['status_message']) ? $response['status_message'] : 'Unknown error'];
            logger()->error("Ebanx ERROR transaction", ['response' => $response]);
        }
        
        //unset payment data for FE
        if (!empty($response)) {
            unset($response['payment']);
        }

        return $result ? ['status' => "SUCCESS"] : $response;
    }   
}
