<?php

namespace App\Services;
use App\Models\Localize;
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
        $email = strtolower($data['email']);
        // separate firstOrCreate method
        $model = OdinCustomer::getByEmail($email);
        if (!$model) {
            $model = new OdinCustomer();
            $model->email = $email;
            $model->first_name = $data['first_name'] ?? '';
            $model->last_name = $data['last_name'] ?? '';
            $model->save();
        }
        //$model = OdinCustomer::firstOrCreate(['email' => strtolower($data['email'])]);
        $model->fill($data);
        if (!empty($data['page'])) {
            $model->last_page_checkout = $data['page'];
        }
        if (!empty($data['sku'])) {
            $model->last_viewed_sku_code = $data['sku'];
        }

        // if type is buyer, first name and last name should not be changed
        if ($model->type == OdinCustomer::TYPE_BUYER) {
            $model->first_name = $model->getOriginal('first_name') ?? $data['first_name'];
            $model->last_name = $model->getOriginal('last_name') ?? $data['last_name'];
        }
        $data['ip'] = request()->ip();

        // prepare array fields
        $this->setArrayFields($data, $model);

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
     * @param array $data
     * @param OdinCustomer $model
     * @return array
     */
    private function getUpdatedAddresses(array $data, $model): array {
        $address = [
            'country' => !empty($data['country']) ? trim($data['country']) : '',
            'zip' => !empty($data['zip']) ? trim($data['zip']) : '',
            'state' => !empty($data['state']) ? trim($data['state']) : '',
            'city' => !empty($data['city']) ? trim($data['city']) : '',
            'street' => !empty($data['street']) ? trim($data['street']) : '',
            'street2' => !empty($data['street2']) ? trim($data['street2']) : '',
            'apt' => !empty($data['apt']) ? trim($data['apt']) : '',
            'building' => !empty($data['building']) ? trim($data['building']) : '',
        ];
        if(!array_filter($address)) {
            $model_addresses = $model->addresses;
        } else {
            $addressJson = json_encode($address);
            $modelAddressesJson = json_encode($model->addresses);
            // add address if not in array
            if (!strstr(' ' . $modelAddressesJson, $addressJson)) {
                $model_addresses = array_merge($model->addresses, [$address]);
            } else {
                $model_addresses = $model->addresses;
            }
        }

        return $model_addresses;
    }

    /**
     * Prepare set array fields in model
     * @param array $data
     * @param $model
     * @return bool
     */
    private function setArrayFields(array $data, $model): bool {
        // add fingerprint if not in array
        if (!empty($data['f']) && !in_array($data['f'], $model->fingerprints ?? [])) {
            $model->fingerprints = array_merge($model->fingerprints ?? [], [$data['f']]);
        }

        // add phone if not in array
        if (!empty($data['phone']) && !in_array($data['phone'], $model->phones ?? [])) {
            $model->phones = array_merge($model->phones ?? [], [$data['phone']]);
        }

        // doc_ids
        if (!empty($data['doc_id']) && !in_array($data['doc_id'], $model->doc_ids ?? [])) {
            $model->doc_ids = array_merge($model->doc_ids ?? [], [$data['doc_id']]);
        }

        // add ip if not in array
        if (!empty($data['ip']) && !in_array($data['ip'], $model->ip ?? [])) {
            $model->ip = array_merge($model->ip ?? [], [$data['ip']]);
        }

        return true;
    }

    /**
     * Get locale customer by number
     * @param string $number
     * @return null
     */
    public static function getLocaleCustomerByNumber(string $number) {
        $customer = OdinCustomer::getByNumber($number);
        $lp = null;
        if ($customer) {
            $lp = new Localize();
            $lp->email = $customer->email;
            $lp->first_name = $customer->first_name;
            $lp->last_name = $customer->last_name;
            $lp->phone = $customer->getLastPhone();
            $lp->address = $customer->getLastAddress();
        }
        return $lp;
    }

}
