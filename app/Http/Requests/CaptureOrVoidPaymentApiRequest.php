<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaptureOrVoidPaymentApiRequest extends FormRequest
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
                'order_id' => $this->route('orderId'),
                'txn_hash' => $this->route('hash'),
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
            'order_id' => ['required', 'string', 'size:24'],
            'txn_hash' => ['required', 'string']
        ];
    }
}
