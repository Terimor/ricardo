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
        
        $validator = $model->validate();
        
        if ($validator->fails()) {
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