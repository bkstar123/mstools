<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCFFWRuleRequest extends FormRequest
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
            'zones' => 'required',
            'description' => 'required',
            'products' => 'required_if:new_action,bypass',
        ];
    }

    /**
     * Provide the custom validation error messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'products.required_if' => 'The features field is required for bypass action'
        ];
    }
}
