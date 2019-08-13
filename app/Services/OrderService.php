<?php

namespace App\Services;
use App\Models\Setting;
use App\Models\Txn;
use App\Models\OdinOrder;

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
    public function addTxn(array $data): array
    {       
        
        $model = new Txn($data);        
        
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
                'txn' => $model->attributesToArray()
             ];
        }
    }
    
    /**
     * 
     * @param array $data
     * @return type
     */
    public function addOdinOrder(array $data): array
    {         
        $model = new OdinOrder($data);
        if (!isset($model->number) || !$model->number) {
            //TODO add country code
            $model->number = $model->generateOrderNumber();
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
                'order' => $model->attributesToArray()
             ];
        }
    }    
}