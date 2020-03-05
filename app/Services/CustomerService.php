<?php

namespace App\Services;
use App\Models\OdinCustomer;

/**
 * Customer Service class
 */
class CustomerService
{
    /**
     * Updates or adds a customer
     * @param array $data
     * @param boolean $returnModel
     * @return array
     */
    public function addOrUpdate(array $data, bool $returnModel = false): array
    {
        $model = OdinCustomer::firstOrNew(['email' => strtolower($data['email'])]);
        $model->fill($data);

        if ($model->type == 'buyer') {
            $model->first_name = $model->getOriginal('first_name');
            $model->last_name = $model->getOriginal('last_name');
        }

        // add ip if not in array
        $data['ip'] = !empty($data['ip']) ? $data['ip'] : request()->ip();

        // prepare array fields
        $array_fields = ['fingerprint' => 'fingerprint', 'phone' => 'phones', 'doc_id' => 'doc_ids', 'ip'=>'ip'];
        foreach ($array_fields as $key => $value) {
            if (!empty($data[$key]) && !in_array($data[$key], $model->{$value} ?? [])) {
                $model->{$value} = array_merge($model->{$value} ?? [], [$data[$key]]);
            }
        }

		// add language code
		if (!$model->language) {
            $model->language = app()->getLocale();
        }

        // addresses
        $model->addresses = $this->getUpdatedAddresses($data, $model);

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
     * Prepare addresses field
     * @param  array  $data
     * @param  OdinCustomer  $model
     * @return array
     */
    private function getUpdatedAddresses(array $data, $model) :array {
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
            $model_addresses = array_merge($model->addresses, [$address]);
        } else {
            $model_addresses = $model->addresses;
        }

        return $model_addresses;
    }
}
