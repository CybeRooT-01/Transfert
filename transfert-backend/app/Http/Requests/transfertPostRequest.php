<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class transfertPostRequest extends FormRequest
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
            "montant"=> "required|numeric |max:1000000",
            "fournisseur"=> "string| in:wave,orangemoney,wari,cb",
            "avec_code"=> "boolean",
            "type"=> "string| required | in:depot,retrait,transfert",
            "numero_telephone_destinataire" => "string",
            "numero_compte_desti"=> "string",
            "permanant"=> "boolean",
            "envoyeur"=> "string",
        ];
    }
    public function messages(): array
    {
        return [
            'montant.required' => 'Le montant est obligatoire',
            'fournisseur.in' => 'Le fournisseur doit être wave, orangemoney, wari ou cb',
            'montant.max' => 'Le montant doit être inférieur à 1000000',
            'montant.numeric' => 'Le montant doit être un nombre',
            'fournisseur.required' => 'Le fournisseur est obligatoire',
            'type.required' => 'Le type est obligatoire',
            'type.string' => 'Le type doit être une chaine de caractère',
            'type.in' => 'Le type doit être depot, retrait ou transfert',
            'compte_destinataire_id.required' => 'Le compte destinataire est obligatoire',
            'avec_code.boolean' => 'Le avec code doit être oui ou non',
            "permanant.boolean" => "Le permant doit être oui ou non",
            "envoyeur.required" => "L'envoyeur est obligatoire",
        ];
    }
}

