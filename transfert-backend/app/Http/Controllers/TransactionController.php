<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionRessources;

class TransactionController extends Controller
{
    public function annulerTransaction(){
        $dateActuel = now()->format('Y-m-d');
        $hier = now()->subDay()->format('Y-m-d');
        $transactions = Transaction::whereBetween('date_transaction',[$hier,$dateActuel])->where('etat', 1)->get();
        return TransactionRessources::collection($transactions);
    }
    public function annulerTransactionById($id){
        $transaction = Transaction::find($id);
        if($transaction){
            $transaction->etat = 0;
            $transaction->save();
            return response()->json(['message'=>'Transaction annulee avec succes', 'status' =>'success'], 200);
        }
        return response()->json(['message'=>'Transaction non trouvee'], 404);
    }
}
