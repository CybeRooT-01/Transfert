<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use App\Http\Requests\transfertPostRequest;

class CompteController extends Controller
{
    public function getClientByCompte($numeroCompte ){
        $compte = Compte::where('numero_compte', $numeroCompte)->first();
        $client = Client::find($compte->client_id);
        if($client){
            return response()->json($client, 200);
        }else{
            return response()->json(['message' => 'client introuvable'], 404);
        }
    }
    public function transaction(transfertPostRequest $request)
    {
        $type = $request->input('type');
        $montant = $request->input('montant');
        $fournisseur = $request->input('fournisseur');
        $envoyeur = $request->input('envoyeur');
        //si le compte existe
        $compteEnvoyeur = Compte::where('numero_compte', $envoyeur)->first();
        if (!$compteEnvoyeur) {
            return response()->json(['message' => 'compte introuvable']);
        }
        switch ($fournisseur) {
            case 'cb':
                if ($montant < 10000) {
                    return response()->json(['message' => 'Le montant minimum est de 10000'], 400);
                }
                break;
            case 'wari':
                if ($montant < 1000) {
                    return response()->json(['message' => 'Le montant minimum est de 1000'], 400);
                }
                break;
            case 'wave' || 'orangemoney':
                if ($montant < 500) {
                    return response()->json(['message' => 'Le montant minimum est de 500'], 400);
                }
                break;
            default:
                return response()->json(['message' => 'Fournisseur non pris en charge'], 400);
                break;
        }
        //gerer les code
        $avecCode = $request->input('avec_code');
        $codeTransaction = null;
        if ($avecCode) {
            if ($type == 'transfert') {
                $codeTransaction = $this->generateRandomcode(25);
            } elseif ($type == 'depot') {
                $codeTransaction = $this->generateRandomcode(15);
            } else {
                return response()->json(['message' => "code de transaction est non requis pour les transaction sans code"], 400);
            }
        }
        // return $codeTransaction;
        //l'envoyeur
        $numeroCompteEnvoyeur = $compteEnvoyeur->numero_compte;
        $fournisseurEnvoyeur = strtolower($numeroCompteEnvoyeur[0].$numeroCompteEnvoyeur[1]);
        //le receveur
        $numeroCompteCible = $request->input('numero_compte_desti');
        $fournisseurCible = strtolower($numeroCompteCible[0].$numeroCompteCible[1]);
        $compteCible = Compte::where('numero_compte', $numeroCompteCible)->first();

        //gerer les transfert compte a combte b truc machin bidule
        if ($type == 'transfert') {
            if ($compteCible == null) {
                return response()->json(['message' => "compte introuvable"], 404);
            }
            if ($fournisseurCible =='cb' && $fournisseur != 'cb') {
                return response()->json(['message' => "Le compte cible doit être un compte bancaire"], 400);
            }elseif($fournisseurCible =='wr' && $fournisseur != 'wari'){
                return response()->json(['message' => "Le compte cible doit être un compte wari"], 400);
            }elseif($fournisseurCible =='om' && $fournisseur != 'orangemoney'){
                return response()->json(['message' => "Le compte cible doit être un compte orangemoney"], 400);
            }elseif($fournisseurCible =='wv' && $fournisseur != 'wave'){
                return response()->json(['message' => "Le compte cible doit être un compte wave"], 400);
            }
            if($fournisseurEnvoyeur != $fournisseurCible){
                return response()->json(['message' => "Le recepteur doit etre du meme fournisseur que l'envoyeur"], 400);
            }
        }
        //gerer les frais
        $frais = 0;
        switch ($fournisseur) {
            case 'orangemoney':
            case 'wave':
                $frais = $montant *0.01;
                break;
            case 'wari':
                $frais = $montant * 0.02;
                break;
            case 'cb':
               $frais = $montant * 0.05;
                break;
            default:
            $frais = 0;
        }
      //transferer
      if ($type == 'transfert' || $type == 'depot') {
        $soldeEnvoyeur = $compteEnvoyeur->solde;
        $soleReceveur = $compteCible->solde;
        if ($soldeEnvoyeur < $montant + $frais) {
            return response()->json(['message' => "solde insuffisant"], 400);
        }
        $compteEnvoyeur->solde = $soldeEnvoyeur - $montant - $frais;
        $compteCible->solde = $soleReceveur + $montant;
        $compteEnvoyeur->save();
        $compteCible->save();
        Transaction::create([
            'montant' => $montant,
            'frais' => $frais,
            'code_transaction' => $codeTransaction,
            'envoyeur_id' => $compteEnvoyeur->id,
            'receveur_id' => $compteCible->id,
            'type_transaction' => $type,
            'date_transaction' => now(),
            'permanent' => false,
        ]);
        return response()->json(['message' => "transfert effectué avec succes", 'frais' => $frais, 'code' => $codeTransaction], 200);
      }elseif ($type =='retrait') {
        $soldeEnvoyeur = $compteEnvoyeur->solde;
        if ($soldeEnvoyeur < $montant + $frais) {
            return response()->json(['message' => "solde insuffisant"], 400);
        };
        $compteEnvoyeur->solde = $soldeEnvoyeur - $montant - $frais;
        $compteEnvoyeur->save();
        Transaction::create([
            'montant' => $montant,
            'frais' => $frais,
            'code_transaction' => $codeTransaction,
            'envoyeur_id' => $compteEnvoyeur->id,
            'receveur_id' => null,
            'type_transaction' => $type,
            'date_transaction' => now(),
            'permanent' => false,
        ]);
      }
    }

    // public function transaction($id, transfertPostRequest $request)
    // {
    //     $type = $request->input('type');
    //     $montant = $request->input('montant');
    //     $fournisseur = $request->input('fournisseur');
    //     //si le compte existe
    //     $compteEnvoyeur = Compte::find($id);
    //     if (!$compteEnvoyeur) {
    //         return response()->json(['message' => 'compte introuvable']);
    //     }
    //     switch ($fournisseur) {
    //         case 'cb':
    //             if ($montant < 10000) {
    //                 return response()->json(['message' => 'Le montant minimum est de 10000'], 400);
    //             }
    //             break;
    //         case 'wari':
    //             if ($montant < 1000) {
    //                 return response()->json(['message' => 'Le montant minimum est de 1000'], 400);
    //             }
    //             break;
    //         case 'wave' || 'orangemoney':
    //             if ($montant < 500) {
    //                 return response()->json(['message' => 'Le montant minimum est de 500'], 400);
    //             }
    //             break;
    //         default:
    //             return response()->json(['message' => 'Fournisseur non pris en charge'], 400);
    //             break;
    //     }
    //     //gerer les code
    //     $avecCode = $request->input('avec_code');
    //     $codeTransaction = null;
    //     if ($avecCode) {
    //         if ($type == 'transfert') {
    //             $codeTransaction = $this->generateRandomcode(25);
    //         } elseif ($type == 'depot') {
    //             $codeTransaction = $this->generateRandomcode(15);
    //         } else {
    //             return response()->json(['message' => "code de transaction est non requis pour les transaction sans code"], 400);
    //         }
    //     }
    //     // return $codeTransaction;
    //     //l'envoyeur
    //     $numeroCompteEnvoyeur = $compteEnvoyeur->numero_compte;
    //     $fournisseurEnvoyeur = strtolower($numeroCompteEnvoyeur[0].$numeroCompteEnvoyeur[1]);
    //     //le receveur
    //     $numeroCompteCible = $request->input('numero_compte_desti');
    //     $fournisseurCible = strtolower($numeroCompteCible[0].$numeroCompteCible[1]);
    //     $compteCible = Compte::where('numero_compte', $numeroCompteCible)->first();

    //     //gerer les transfert compte a combte b truc machin bidule
    //     if ($type == 'transfert') {
    //         if ($compteCible == null) {
    //             return response()->json(['message' => "compte introuvable"], 404);
    //         }
    //         if ($fournisseurCible =='cb' && $fournisseur != 'cb') {
    //             return response()->json(['message' => "Le compte cible doit être un compte bancaire"], 400);
    //         }elseif($fournisseurCible =='wr' && $fournisseur != 'wari'){
    //             return response()->json(['message' => "Le compte cible doit être un compte wari"], 400);
    //         }elseif($fournisseurCible =='om' && $fournisseur != 'orangemoney'){
    //             return response()->json(['message' => "Le compte cible doit être un compte orangemoney"], 400);
    //         }elseif($fournisseurCible =='wv' && $fournisseur != 'wave'){
    //             return response()->json(['message' => "Le compte cible doit être un compte wave"], 400);
    //         }
    //         if($fournisseurEnvoyeur != $fournisseurCible){
    //             return response()->json(['message' => "Le recepteur doit etre du meme fournisseur que l'envoyeur"], 400);
    //         }
    //     }
    //     //gerer les frais
    //     $frais = 0;
    //     switch ($fournisseur) {
    //         case 'orangemoney':
    //         case 'wave':
    //             $frais = $montant *0.01;
    //             break;
    //         case 'wari':
    //             $frais = $montant * 0.02;
    //             break;
    //         case 'cb':
    //            $frais = $montant * 0.05;
    //             break;
    //         default:
    //         $frais = 0;
    //     }
    //   //transferer
    //   if ($type == 'transfert') {
    //     $soldeEnvoyeur = $compteEnvoyeur->solde;
    //     $soleReceveur = $compteCible->solde;
    //     if ($soldeEnvoyeur < $montant + $frais) {
    //         return response()->json(['message' => "solde insuffisant"], 400);
    //     }
    //     $compteEnvoyeur->solde = $soldeEnvoyeur - $montant - $frais;
    //     $compteCible->solde = $soleReceveur + $montant;
    //     $compteEnvoyeur->save();
    //     $compteCible->save();
    //     Transaction::create([
    //         'montant' => $montant,
    //         'frais' => $frais,
    //         'code_transaction' => $codeTransaction,
    //         'envoyeur_id' => $compteEnvoyeur->id,
    //         'receveur_id' => $compteCible->id,
    //         'type_transaction' => $type,
    //         'date_transaction' => now(),
    //         'permanent' => false,
    //     ]);
    //     return response()->json(['message' => "transfert effectué avec succes", 'frais' => $frais, 'code' => $codeTransaction], 200);
    //   }elseif ($type == 'depot') {
    //     //depot
    //   }elseif ($type =='retrait') {
    //     //retrait
    //   }
    // }

    private function generateRandomcode($longueur){
        $code = '';
        $chaine = '1234567890';
        $longueurChaine = strlen($chaine);
        for ($i=0; $i < $longueur; $i++) {
            $code.=$chaine[random_int(0, $longueurChaine - 1)];
        }
        return $code;
    }
}
