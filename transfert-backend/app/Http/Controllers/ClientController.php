<?php

namespace App\Http\Controllers;

use App\Http\Requests\transfertPostRequest;
use App\Http\Resources\ClientRessource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
    public function show(string $id)
    {
        $client = Client::find($id);
        return $client ?? response()->json(['message'=>'Client Introuvable'], Response::HTTP_NOT_FOUND);
    }

    public function getCompteByClient($id){
        $client = Client::find($id);
        if($client){
            return new ClientRessource($client);
        }else{
            return response()->json(['message'=>'Client Introuvable'], Response::HTTP_NOT_FOUND);
        }
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
