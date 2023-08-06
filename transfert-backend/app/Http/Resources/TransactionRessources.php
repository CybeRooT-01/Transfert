<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionRessources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id' => $this->id,
            'date_transaction' => $this->date_transaction,
            'montant' => $this->montant,
            'frais' => $this->frais,
            'envoyeur_nom'=>$this->compte->client->nom,
            'envoyeur_prenom'=>$this->compte->client->prenom,
       ];
    }
}
