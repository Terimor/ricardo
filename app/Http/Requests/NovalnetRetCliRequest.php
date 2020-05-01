<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class NovalnetRetCliRequest extends FormRequest
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
                'tsec' => $this->request->get('txn_secret')
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
            'checksum' => ['required', 'string'],
            'tsec' => ['required', 'string'],
            'tid' => ['required', 'string']
        ];
    }

    /**
     *
     * @param Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        logger()->error('Novalnet ret cli', ['errors' => $validator->errors()]);

        $response = new JsonResponse(['errors' => $validator->errors()], 422);
        throw new ValidationException($validator, $response);
    }
}
