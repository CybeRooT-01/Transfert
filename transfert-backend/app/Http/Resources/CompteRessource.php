<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'numero_compte' => $this->numero_compte,
            'fournisseur' => $this->fournisseur,
            'solde' => $this->solde,
            'client_id' => $this->client_id,
        ];
    }
}
