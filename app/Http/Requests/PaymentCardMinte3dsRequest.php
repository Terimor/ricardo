<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentCardMinte3dsRequest extends FormRequest
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

    // Setup the validation of query/route parameters
    public function all($keys = null)
    {
        return \array_merge(
            parent::all(),
            [
                'orderid' => $this->route('orderId'),
            ]
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'orderid'   => ['required', 'string', 'size:24'],
            'transid'   => ['required', 'string'],
            'status'    => ['required', 'string', 'in:SUCCESS,FAILED'],
            'signature' => ['required', 'string'],
            'token'     => ['string'],
            'timestamp' => ['string'],
            'errorcode' => ['string'],
            'errormessage' => ['string'],
        ];
    }
}
