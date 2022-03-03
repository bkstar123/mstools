<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CFFWRuleRequest extends FormRequest
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
            'action' => 'required',
            'products' => 'required_if:action,bypass',
            'expression' => 'required'
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
