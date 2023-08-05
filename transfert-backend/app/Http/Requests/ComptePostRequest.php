<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComptePostRequest extends FormRequest
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
            'numero_telephone' => 'required|string',
            'fournisseur' => 'required|string|in:wave,orangemoney,cb,wari',
        ];
    }
    public function messages()
    {
        return [
            'numero_telephone.required' => 'Le numero de telephone est obligatoire',
            'fournisseur.required' => 'Le fournisseur est obligatoire',
            'fournisseur.in' => 'Le fournisseur doit etre wave ou orangemoney ou cb ou wari',
        ];
    }
}
