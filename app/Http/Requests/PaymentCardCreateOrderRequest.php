<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\PaymentService;

class PaymentCardCreateOrderRequest extends FormRequest
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
            'address.city'                  => ['required', 'string'],
            'address.country'               => ['required', 'regex:/^[a-z]{2}$/'],
            'address.state'                 => ['string', 'between:1,30'],
            'address.street'                => ['required', 'string'],
            'address.district'              => ['string', 'between:1,255'],
            'address.building'              => ['string', 'between:1,9'],
            'address.complement'            => ['nullable', 'string', 'between:0,255'],
            'address.zip'                   => ['required', 'string'],
            'contact.email'                 => ['required', 'email:rfc'],
            'contact.first_name'            => ['required', 'string'],
            'contact.last_name'             => ['required', 'string'],
            'contact.phone.number'          => ['required', 'regex:/^\d+$/'],
            'contact.phone.country_code'    => ['required', 'regex:/^\d{1,7}$/'],
            'contact.document_number'       => ['string'],
            'card.number'                   => ['required', 'regex:/^\d+$/'],
            'card.type'                     => ['string', 'in:' . PaymentService::CARD_CREDIT . ',' . PaymentService::CARD_DEBIT],
            'card.month'                    => ['required', 'regex:/^(0?[1-9]|1[012])$/'],
            'card.year'                     => ['required', 'regex:/^20\d{2}$/'],
            'card.cvv'                      => ['required', 'regex:/^\d{3,4}$/'],
            'card.installments'             => ['integer', 'between:0,6'],
            'product.qty'                   => ['required', 'integer', 'between:1,5'],
            'product.sku'                   => ['required', 'string'],
            'product.is_warranty_checked'   => ['boolean'],
            'kount_session_id'              => ['nullable', 'string', 'between:1,32'],
            'order'                         => ['string', 'size:24'],
            'ipqs'                          => ['nullable'],
            'f'                             => ['string', 'between:32,32']
        ];
    }
}
