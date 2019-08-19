<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EbanxSendTransactionRequest extends FormRequest
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
            'amount_total' => [
                'required',
            ],
            'name' => [
                'required',
                'string',
            ],
            'email' => [
                'required',
                'email'
            ],
            'birth_date' => [
                'required',
                'string'
            ],
            'address' => [
                'required',
                'string'
            ],
            'street_number' => [
                'required',
                'string'
            ],
            'city' => [
                'required',
                'string'
            ],            
            'state' => [
                'required',
                'string'
            ],
            'zipcode' => [
                'required',
                'string'
            ],
            'phone_number' => [
                'required',
                'string'
            ],
            'payment_type_code' => [
                'required',
                'string'
            ],            
            'token' => [
                'required',
                'string'
            ],
        ];
    }
}
