<?php

namespace App\Http\Requests;

use App\Constants\PaymentMethods;
use Illuminate\Foundation\Http\FormRequest;

class CreateApmOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address.city'  => ['required', 'string'],
            'address.zip'   => ['required', 'string'],
            'address.state' => ['string', 'between:1,30'],
            'address.country'   => ['required', 'regex:/^[a-z]{2}$/'],
            'address.street'    => ['required', 'string'],
            'address.district'  => ['string', 'between:1,255'],
            'address.building'  => ['string', 'between:1,9'],
            'address.complement' => ['nullable', 'string', 'between:0,255'],
            'contact.email' => ['required', 'email:rfc'],
            'contact.last_name' => ['required', 'string'],
            'contact.first_name'    => ['required', 'string'],
            'contact.phone.number'  => ['required', 'regex:/^\d+$/'],
            'contact.document_number' => ['string', 'regex:/[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}/'],
            'contact.phone.country_code' => ['required', 'regex:/^\d{1,7}$/'],
            'product.qty'   => ['required', 'integer', 'between:1,5'],
            'product.sku'   => ['required', 'string'],
            'product.is_warranty_checked' => ['boolean'],
            'method' => ['required', 'string', 'in:' . implode(',', PaymentMethods::getAllActive(true))],
            'order' => ['string', 'size:24'],
            'ipqs'  => ['nullable'],
            'f'     => ['string', 'between:32,32']
        ];
    }
}
