<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentCardCreateUpsellsOrderRequest extends FormRequest
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
            'order'         => ['required', 'string', 'size:24'],
            'upsells.*.id'  => ['required', 'string', 'size:24'],
            'upsells.*.qty' => ['required', 'integer', 'between:1,10']
        ];
    }
}
