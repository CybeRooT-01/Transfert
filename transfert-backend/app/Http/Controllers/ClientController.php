<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ClientRessource;
use App\Http\Requests\CreateClientRequest;
use App\Http\Requests\transfertPostRequest;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Client::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateClientRequest $request)
    {
        $numeroTelephone = $request->numero_telephone;
        $regex = "/^(77|78|76|75|70)[0-9]{7}$/";
        if(!preg_match($regex, $numeroTelephone)){
            return response()->json(['message'=>'Le numero de telephone doit contenir 9 chiffres et doit commencer par 77,78,76,75,70'], Response::HTTP_BAD_REQUEST);
        }
        $client = Client::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'numero_telephone' => $numeroTelephone,
        ]);
        return response()->json([
            'message'=>'Client cree avec succes',
            'client' => $client
        ], Response::HTTP_CREATED);
    }
   
    /**
     * Display the specified resource.
     */
    public function getclientsTransaction(string $id)
    {
        $transactions = Transaction::where('envoyeur_id', $id)->orWhere('receveur_id', $id)->get();
        $transactions = $transactions->map(function($transaction){
            return [
                'type_transaction' => $transaction->type_transaction,
                'montant' => $transaction->montant,
                'date_transaction' => $transaction->date_transaction,
                'frais' => $transaction->frais,
                'date_expiration' => $transaction->date_expiration
            ];
        });
        return $transactions;
    }

    public function getCompteByClient($id){
        $client = Client::find($id);
        if($client){
            return new ClientRessource($client);
        }else{
            return response()->json(['message'=>'Client Introuvable'], Response::HTTP_NOT_FOUND);
        }
    }
    public function getTransactionsByClient($clientName){

    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
