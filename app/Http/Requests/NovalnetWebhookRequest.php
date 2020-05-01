<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class NovalnetWebhookRequest extends FormRequest
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
        return array_merge(
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
            'orderid' => ['required', 'string', 'size:24'],
            'event' => ['required', 'array'],
            'transaction' => ['required', 'array'],
        ];
    }

    /**
     *
     * @param Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        logger()->error('Novalnet webhook', ['errors' => $validator->errors()]);

        $response = new JsonResponse(['errors' => $validator->errors()], 422);
        throw new ValidationException($validator, $response);
    }
}
