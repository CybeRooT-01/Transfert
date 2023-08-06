<?php

namespace App\Http\Controllers;

use App\Http\Requests\BloqueDebloquePutRequest;
use App\Http\Requests\CompteCloseRequest;
use App\Http\Requests\ComptePostRequest;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Transaction;
use App\Http\Requests\transfertPostRequest;

class CompteController extends Controller
{
    public function bloquerDebloquerCompte(BloqueDebloquePutRequest $request){
        $numCompte = $request->numero_compte;
        $compte = Compte::where('numero_compte', $numCompte)->where('statut', 1)->first();
        if($compte->bloquer == 1){
            $compte->bloquer = 0;
            $compte->save();
            return response()->json([
                'message' => 'Compte débloqué avec succées.',
                'compte'=>$compte
            ], 200);
        }else{
            $compte->bloquer = 1;
            $compte->save();
            return response()->json([
                'message' => 'Compte bloqué avec succées.',
                'compte'=>$compte
            ], 200);
        }
    }
    public function fermerCompte(CompteCloseRequest $request){
        $numCompte = $request->numero_compte;
        $raison = $request->raisons;
        $grosMots = ['con','merde','putain','batard','pd','encule','enculé','chier', 'shit'];
        foreach ($grosMots as $mot) {
            if (stripos($raison, $mot) !== false) {
                return response()->json(['message' => 'Les raisons contiennent des mots interdits.'], 400);
            }
        }
        $compte = Compte::where('numero_compte', $numCompte)->where('statut', 1)->first();
        if(!$compte){
            return response()->json(['message' => "Ce compte n'existe pas."], 404);
        }
        $compte->statut = 0;
        $compte->raison_de_fermeture = $raison;
        $compte->save();
        return response()->json([
            'message' => 'Compte fermé avec succées.',
            'compte'=>$compte
        ]);
    }

    public function store(ComptePostRequest $request)
    {
        $numTel = $request->numero_telephone;
        $fournisseur = $request->fournisseur;
        $regex = "/^(77|78|76|75|70)[0-9]{7}$/";
        if (!preg_match($regex, $numTel)) {
            return response()->json(['message' => 'Le numero de telephone doit contenir 9 chiffres et doit commencer par 77,78,76,75,70'], 400);
        }
        $client = Client::where('numero_telephone', $numTel)->first();
        if (!$client) {
            return response()->json(['message' => "Ce client n'existe pas, veillez d'abord l'inscrire "], 400);
        }
        $numCompte = $this->createNumCompte($numTel, $fournisseur);
        $compte = Compte::where('client_id', $client->id)->where('statut', 1)->first();
        if ($compte) {
            return response()->json(['message' => 'Ce numero a deja un compte chez ce fournisseur'], 400);
        }
        Compte::create([
            'numero_compte' => $numCompte,
            'client_id' => $client->id,
            'solde' => 0,
            'fournisseur' => $fournisseur,
        ]);
        return response()->json([
            'message' => 'Compte cree avec succes',
            'numero_compte' => $numCompte,
        ], 201);
    }
    private function createNumCompte(String $numeroCompte, String $fournisseur)
    {
        if ($fournisseur == 'wari') {
            $numeroCompte = 'WR-' . $numeroCompte;
        } elseif ($fournisseur == 'cb') {
            $numeroCompte = 'CB-' . $numeroCompte;
        } elseif ($fournisseur == 'orangemoney') {
            $numeroCompte = 'OM-' . $numeroCompte;
        } elseif ($fournisseur == 'wave') {
            $numeroCompte = 'WV-' . $numeroCompte;
        }
        return $numeroCompte;
    }

    private function checkEnvoyabilite($numCompteenvoyeur, $numeroCompteCible, $fournisseur)
    {
        $fournisseurCible = strtolower($numeroCompteCible[0] . $numeroCompteCible[1]);
        $fournisseurEnvoyeur = strtolower($numCompteenvoyeur[0] . $numCompteenvoyeur[1]);
        if ($fournisseur == 'wari' && ($fournisseurEnvoyeur != 'wr')) {
            return response()->json(['message' => 'le fournisseur wari ne peut effectuer que des transferts entre comptes wari'], 400);
        } elseif ($fournisseur == 'cb' && $fournisseurEnvoyeur != 'cb') {
            return response()->json(['message' => 'le fournisseur carte bancaire ne peut effectuer que des transferts entre comptes carte bancaire'], 400);
        } elseif ($fournisseur == 'orangemoney' && ($fournisseurEnvoyeur != 'om')) {
            return response()->json(['message' => 'le fournisseur orange money ne peut effectuer que des transferts entre comptes orange money'], 400);
        } elseif ($fournisseur == 'wave' && ($fournisseurEnvoyeur != 'wv')) {
            return response()->json(['message' => 'le fournisseur wave ne peut effectuer que des transferts entre comptes wave'], 400);
        }
    }
    private function traiterDepot($type, $fournisseur, $fournisseurShort, $avecCode, $montant, $frais, $NumclientEnvoyeur, $numCompteenvoyeur, $numeroCompteCible)
    {
        if (!$avecCode) {
            $codeTransaction = $this->generateRandomcode(15);
            $client = Client::where('numero_telephone', $NumclientEnvoyeur)->first();
            if (!$client) {
                return response()->json(['message' => 'il faut au moins un numero de telephone'], 400);
            }
            $fournisseurCible = strtolower($numeroCompteCible[0] . $numeroCompteCible[1]);
            $fournisseurEnvoyeur = strtolower($numCompteenvoyeur[0] . $numCompteenvoyeur[1]);

            if ($fournisseurCible !== $fournisseurShort  || $fournisseurEnvoyeur !== $fournisseurShort) {
                return response()->json(['message' => 'le depot ' . $fournisseur . ' ne peut se faire que sur un compte ' . $fournisseur], 400);
            }
            $data =  $this->checkEnvoyabilite($numCompteenvoyeur, $numeroCompteCible, $fournisseur);
            if ($data != null) {
                return $data;
            }

            $compteCible = Compte::where('numero_compte', $numeroCompteCible)->where('statut', 1)->first();
            if (!$compteCible) {
                return response()->json(['message' => 'compte introuvable'], 400);
            }
            if($compteCible->bloquer == 1){
                return response()->json(['message' => 'retrait impossible sur un compte bloquer'], 400);
            }
            $compteCible->solde += $montant;
            $compteCible->save();
            Transaction::create([
                'montant' => $montant,
                'type_transaction' => $type,
                'date_transaction' => Carbon::now(),
                'envoyeur_id' => null,
                'receveur_id' => $compteCible->id,
                'frais' => $frais,
                'permanent' => false,
                'code_transaction' => $codeTransaction
            ]);
            return response()->json([
                'message' => 'depot effectue avec succes',
                'code' => $codeTransaction,
                'frais' => $frais,
            ], 200);
        } else {
            $compteEnvoyeur = Compte::where('numero_compte', $numCompteenvoyeur)->first();
            $data = $this->checkEnvoyabilite($numCompteenvoyeur, $numeroCompteCible, $fournisseur);
            if ($data != null) {
                return $data;
            }
            if (!$compteEnvoyeur) {
                return response()->json(['message' => 'compte envoyeur introuvable'], 400);
            } else {
                $montantTotal = $montant + $frais;
                if ($compteEnvoyeur->solde < $montantTotal) {
                    return response()->json(['message' => 'solde insuffisant'], 400);
                }
                $compteEnvoyeur->solde -= $montantTotal;
                $codeTransaction = $this->generateRandomcode(25);
                $compteEnvoyeur->save();
                Transaction::create([
                    'montant' => $montant,
                    'type_transaction' => $type,
                    'date_transaction' => Carbon::now(),
                    'envoyeur_id' => $compteEnvoyeur->id,
                    'receveur_id' => null,
                    'frais' => $frais,
                    'permanent' => false,
                    'code_transaction' => $codeTransaction
                ]);
                return response()->json([
                    'message' => 'depot effectue avec succes',
                    'code' => $codeTransaction,
                    'frais' => $frais,
                ], 200);
            }
        }
    }
    private function traiterRetrait($numCompteenvoyeur, $numeroCompteCible, $montant, $type, $frais, $fournisseur, $fournisseurShort)
    {
        $compteEnvoyeur = Compte::where('numero_compte', $numCompteenvoyeur)->first();
        $fournisseurEnvoyeur = strtolower($compteEnvoyeur->numero_compte[0] . $compteEnvoyeur->numero_compte[1]);
        if ($fournisseurEnvoyeur !== $fournisseurShort) {
            return response()->json(['message' => 'le retrait ' . $fournisseur . ' ne peut se faire que sur un compte ' . $fournisseur], 400);
        }
        if (!$compteEnvoyeur) {
            return response()->json(['message' => 'compte envoyeur introuvable'], 400);
        }
        if($compteEnvoyeur->bloquer == 1){
            return response()->json(['message' => 'retrait impossible sur un compte bloquer'], 400);
        }
        $montantTotal = $montant + $frais;
        if ($compteEnvoyeur->solde < $montantTotal) {
            return response()->json(['message' => 'solde insuffisant'], 400);
        }

        if ($numeroCompteCible) {
            return response()->json(['message' => "pas besoin d'un destinataire pour un retrait"], 400);
        } else {
            $codeTransaction = $this->generateRandomcode(15);
            $compteEnvoyeur->solde -= $montantTotal;
            $compteEnvoyeur->save();
            Transaction::create([
                'montant' => $montant,
                'type_transaction' => $type,
                'date_transaction' => Carbon::now(),
                'envoyeur_id' => $compteEnvoyeur->id,
                'receveur_id' => null,
                'frais' => $frais,
                'permanent' => false,
                'code_transaction' => $codeTransaction
            ]);
            return response()->json([
                'message' => 'retrait effectue avec succes',
                'code' => $codeTransaction,
                'frais' => $frais,
            ], 200);
        }
    }

    private function traiterTransfert($numCompteenvoyeur, $numeroCompteCible, $montant, $type, $frais, $fournisseur, $permanant)
    {
        $compteEnvoyeur = Compte::where('numero_compte', $numCompteenvoyeur)->where('statut', 1)->first();
        $compteCible = Compte::where('numero_compte', $numeroCompteCible)->where('statut', 1)->first();
        $fournisseurEnvoyeur = strtolower($compteEnvoyeur->numero_compte[0] . $compteEnvoyeur->numero_compte[1]);
        $fournisseurCible = strtolower($numeroCompteCible[0] . $numeroCompteCible[1]);
        if ($fournisseurEnvoyeur !== $fournisseurCible) {
            return response()->json(['message' => 'le transfert ne peut se faire que sur un compte ' . $fournisseur], 400);
        }
        if (!$compteEnvoyeur || !$compteCible) {
            return response()->json(['message' => 'compte introuvable'], 400);
        }
        if($compteEnvoyeur->bloquer == 1){
            return response()->json(['message' => 'transfert impossible sur un compte bloquer'], 400);
        }
        $data = $this->checkEnvoyabilite($numCompteenvoyeur, $numeroCompteCible, $fournisseur);
        if ($data != null) {
            return $data;
        }

        $montantTotal = $montant + $frais;


        if ($permanant === true && $fournisseur == 'cb') {
            $dateActuelle = Carbon::now();
            $codeTransaction = $this->generateRandomcode(30);
            $compteEnvoyeur->solde -= $montantTotal;
            $compteCible->solde += $montant;
            $dateExpiration = Carbon::now()->addHours(24)->toDateTimeString();
            $compteEnvoyeur->save();
            $compteCible->save();
            Transaction::create([
                'montant' => $montant,
                'type_transaction' => $type,
                'date_transaction' => Carbon::now(),
                'envoyeur_id' => $compteEnvoyeur->id,
                'receveur_id' => $compteCible->id,
                'frais' => $frais,
                'permanent' => true,
                'code_transaction' => $codeTransaction,
                'date_expiration' => $dateExpiration
            ]);

            if ($dateActuelle->diffInHours($dateExpiration) > 24) {
                $compteEnvoyeur->solde += $montantTotal;
                $compteCible->solde -= $montant;
                $compteEnvoyeur->save();
                $compteCible->save();
                Transaction::where('code_transaction', $codeTransaction)->delete();
                return response()->json(['message' => "le transfert n'est plus valide"], 400);
            }
            return response()->json([
                'message' => 'transfert permanent effectue avec succes',
                'code' => $codeTransaction,
                'frais' => $frais,
                'date_expiration' => $dateExpiration
            ]);
        } elseif ($permanant === false && $fournisseur != 'cb') {
            return response()->json(['message' => "immediat ne peut etre que sur un compte cb"], 400);
        }
        $compteEnvoyeur->solde -= $montantTotal;
        $compteCible->solde += $montant;
        $codeTransaction = $this->generateRandomcode(15);
        $compteEnvoyeur->save();
        $compteCible->save();
        Transaction::create([
            'montant' => $montant,
            'type_transaction' => $type,
            'date_transaction' => Carbon::now(),
            'envoyeur_id' => $compteEnvoyeur->id,
            'receveur_id' => $compteCible->id,
            'frais' => $frais,
            'permanent' => false,
            'code_transaction' => $codeTransaction
        ]);
        return response()->json([
            'message' => 'transfert effectue avec succes',
            'code' => $codeTransaction,
            'frais' => $frais,
        ], 200);
    }
    private function transfertNonReconnu()
    {
        return response()->json(['message' => 'transfert non reconnu'], 400);
    }


    public function transaction(transfertPostRequest $request)
    {
        $type = $request->input('type');
        $montant = $request->input('montant');
        $avecCode = $request->input('avec_code');
        $fournisseur = $request->input('fournisseur');
        $numCompteenvoyeur = $request->input('numCompteEnvoyeur');
        $numeroCompteCible = $request->input('numero_compte_desti');
        $NumclientEnvoyeur = $request->input('numTelEnvoyeur');
        $permanent = $request->input('permanant');
        $frais = 0;
        switch ($fournisseur) {
            case 'orangemoney':
            case 'wave':
                $frais = $montant * 0.01;
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
        if ($fournisseur === 'wari') {
            $fournisseurShort = 'wr';
            $permanent = true;
            if ($type == 'depot') {
                return $this->traiterDepot($type, $fournisseur, $fournisseurShort, $avecCode, $montant, $frais, $NumclientEnvoyeur, $numCompteenvoyeur, $numeroCompteCible);
            } elseif ($type == 'retrait') {
                return $this->traiterRetrait($numCompteenvoyeur, "", $montant, $type, $frais, $fournisseur, $fournisseurShort);
            } elseif ($type === "transfert") {
                return $this->traiterTransfert($numCompteenvoyeur, $numeroCompteCible, $montant, $type, $frais, $fournisseur, $permanent);
            } else {
                return $this->transfertNonReconnu();
            }
        } elseif ($fournisseur === 'orangemoney') {
            $fournisseurShort = 'om';
            $permanent = true;
            if ($type == 'depot') {
                return $this->traiterDepot($type, $fournisseur, $fournisseurShort, $avecCode, $montant, $frais, $NumclientEnvoyeur, $numCompteenvoyeur, $numeroCompteCible);
            } elseif ($type == 'retrait') {
                return $this->traiterRetrait($numCompteenvoyeur, "", $montant, $type, $frais, $fournisseur, $fournisseurShort);
            } elseif ($type === "transfert") {
                return $this->traiterTransfert($numCompteenvoyeur, $numeroCompteCible, $montant, $type, $frais, $fournisseur, $permanent);
            } else {
                return $this->transfertNonReconnu();
            }
        } elseif ($fournisseur === 'wave') {
            $fournisseurShort = 'wv';
            $permanent = true;
            if ($type == 'depot') {
                return $this->traiterDepot($type, $fournisseur, $fournisseurShort, $avecCode, $montant, $frais, $NumclientEnvoyeur, $numCompteenvoyeur, $numeroCompteCible);
            } elseif ($type == 'retrait') {
                return $this->traiterRetrait($numCompteenvoyeur, "", $montant, $type, $frais, $fournisseur, $fournisseurShort);
            } elseif ($type === "transfert") {
                return $this->traiterTransfert($numCompteenvoyeur, $numeroCompteCible, $montant, $type, $frais, $fournisseur, $permanent);
            } else {
                return $this->transfertNonReconnu();
            }
        } elseif ($fournisseur === 'cb') {
            $fournisseurShort = 'cb';
            if ($type == 'depot') {
                return $this->traiterDepot($type, $fournisseur, $fournisseurShort, $avecCode, $montant, $frais, $NumclientEnvoyeur, $numCompteenvoyeur, $numeroCompteCible);
            } elseif ($type == 'retrait') {
                return $this->traiterRetrait($numCompteenvoyeur, "", $montant, $type, $frais, $fournisseur, $fournisseurShort);
            } elseif ($type === "transfert") {
                return $this->traiterTransfert($numCompteenvoyeur, $numeroCompteCible, $montant, $type, $frais, $fournisseur, $permanent);
            }
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

    public function getClientByCompte($idCompte)
    {
        $idCompte = Compte::where('numero_compte', $idCompte)->where('statut', 1)->first();
        $idClient = $idCompte->client_id;
        $client = Client::where('id', $idClient)->first();
        return response()->json([
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'id' => $client->id,
        ]);
    }

    public function getCompteByNumeroCompte($numeroCompte)
    {
        $compte = Compte::where('numero_compte', $numeroCompte)->where('statut', 1)->first();
        return response()->json([
            'numero_compte' => $compte->numero_compte,
            'id' => $compte->id,
            'bloquer' => $compte->bloquer,
        ]);
    }
}
