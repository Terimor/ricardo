<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayPalCrateOrderRequest extends FormRequest
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
            'sku_code' => [
                'required',
                'string',
            ],
            'sku_quantity' => [
                'required',
                'integer',
                'between:1,5'
            ],
            'is_warranty_checked' => [
                'boolean',
            ],
            'order' => [
                'nullable',
                'string'
            ],
            'page_checkout' => [
                'required',
                'string'
            ],
            'offer' => [
                'nullable',
                'string'
            ],
            'affiliate' => [
                'nullable',
                'string'
            ],
            'ipqs' => [
                'nullable'
            ]
        ];
    }
}
