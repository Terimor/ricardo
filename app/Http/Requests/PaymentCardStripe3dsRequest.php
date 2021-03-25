<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PaymentCardStripe3dsRequest extends FormRequest
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
            'payment_intent'    => ['required', 'string'],
            //'source_type'   => ['required', 'string', 'in:card'],
            'orderid'   => ['required', 'string', 'size:24']
        ];
    }

    /**
     *
     * @param Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        logger()->error('Stripe 3ds redirect', ['errors' => $validator->errors()]);

        $response = new JsonResponse(['errors' => $validator->errors()], 422);
        throw new ValidationException($validator, $response);
    }
}
