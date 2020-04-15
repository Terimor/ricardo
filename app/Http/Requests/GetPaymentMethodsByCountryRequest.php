<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPaymentMethodsByCountryRequest extends FormRequest
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
            'cur' => ['string', 'size:3'],
            'country' => ['required', 'string', 'size:2']
        ];
    }
}
