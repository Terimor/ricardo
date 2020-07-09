<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class ChangeOrderAddressRequest extends FormRequest
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
            'email'                 => ['required', 'email'],
            'code'                  => ['required', 'digits:6'],
            'number'                => ['required', 'string'],
            'city'                  => ['required', 'string'],
            'country'               => ['required', 'regex:/^[a-z]{2}$/'],
            'state'                 => ['string', 'between:1,30', 'nullable'],
            'street'                => ['required', 'string'],
            'district'              => ['string', 'between:1,255', 'nullable'],
            'building'              => ['string', 'between:1,9', 'nullable'],
            'complement'            => ['nullable', 'string', 'between:0,255'],
            'zipcode'               => ['required', 'string'],
        ];
    }
}
