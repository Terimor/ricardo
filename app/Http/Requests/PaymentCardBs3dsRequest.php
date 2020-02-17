<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class PaymentCardBs3dsRequest extends FormRequest
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
            'order_id'  => ['required', 'string', 'size:24'],
            '3ds_ref'   => ['required', 'string']
        ];
    }

    /**
     *
     * @param Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        logger()->error('BS complete 3ds', ['errors' => $validator->errors()]);

        $response = new JsonResponse(['errors' => $validator->errors()], 422);
        throw new ValidationException($validator, $response);
    }
}
