<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use Illuminate\Http\Request;
use App\Http\Requests\transfertPostRequest;
use Nette\Utils\Strings;

class CompteController extends Controller
{

    public function transaction($id, transfertPostRequest $request)
    {
        $type = $request->input('type');
        $montant = $request->input('montant');
        $fournisseur = $request->input('fournisseur');
        $compteEnvoyeur = Compte::find($id);
        //si le compte existe
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
      if ($type == 'transfert') {
        return $compteEnvoyeur;

      }elseif ($type == 'depot') {
      }elseif ($type =='retrait') {
      }
    }

    private function generateRandomcode($longueur)
    {
        $code = '';
        $chaine = '1234567890';
        $longueurChaine = strlen($chaine);
        for ($i = 0; $i < $longueur; $i++) {
            $code .= $chaine[random_int(0, $longueurChaine - 1)];
        }
        return $code;
    }
}
