<?php

namespace App\Services;
use App\Models\Setting;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Models\OdinCustomer;

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
                'txn' => $returnModel ? $model : $model->attributesToArray()
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

        // addresses
        $address = [
            'country' => !empty($data['country']) ? trim($data['country']) : '',
            'zip' => !empty($data['zip']) ? trim($data['zip']) : '',
            'state' => !empty($data['state']) ? trim($data['state']) : '',
            'city' => !empty($data['city']) ? trim($data['city']) : '',
            'street' => !empty($data['street']) ? trim($data['street']) : '',
            'street2' => !empty($data['street2']) ? trim($data['street2']) : '',
        ];

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
}
