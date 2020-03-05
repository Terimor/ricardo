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
        $ip = !empty($data['ip']) ? $data['ip'] : request()->ip();
        if (!in_array($ip, $model->ip)) {
            $model->ip = array_merge($model->ip, [$ip]);
        }

        // add fingerprint if not in array
        if (!empty($data['fingerprint']) && !in_array($data['fingerprint'], $model->fingerprint ?? [])) {
            $model->fingerprint = array_merge($model->fingerprint ?? [], [$data['fingerprint']]);
        }

        // add phone if not in array
        if (!empty($data['phone']) && !in_array($data['phone'], $model->phones)) {
            $model->phones = array_merge($model->phones, [$data['phone']]);
        }

		// doc_ids
		if (!empty($data['doc_id']) && !in_array($data['doc_id'], $model->doc_ids)) {
            $model->doc_ids = array_merge($model->doc_ids, [$data['doc_id']]);
        }
		// add language code
		if (!$model->language) {
            $model->language = substr(app()->getLocale(), 0, 2);
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
