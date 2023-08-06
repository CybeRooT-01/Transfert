<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BloqueDebloquePutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
                "numero_compte" => "required|exists:comptes",
        ];
    }
    public function messages()
    {
        return [
            'numero_compte.required' => 'Le numero de compte est obligatoire',
            'numero_compte.exists' => 'Ce numero de compte n\'existe pas',
        ];
    }
}
