<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ClientRessource;
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function getclientsTransaction(string $id)
    {
        $transactions = Transaction::where('envoyeur_id', $id)->get();
        $transactions = $transactions->map(function($transaction){
            return [
                'type_transaction' => $transaction->type_transaction,
                'montant' => $transaction->montant,
                'date_transaction' => $transaction->date_transaction,
                'frais' => $transaction->frais,
                'date_expiration' => $transaction->date_expiration,
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
