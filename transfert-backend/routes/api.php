<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/clients', [ClientController::class, 'index']);
Route::get('/clients/{id}/transaction', [ClientController::class, 'getclientsTransaction'] );
Route::get('/clients/{id}/compte',[ClientController::class,'getCompteByClient']);
// Route::post('/compte/{idCompte}/transaction', [CompteController::class, 'transaction']);
Route::post('/transaction', [CompteController::class, 'transaction']);


Route::get('/compte/{idCompte}/client', [CompteController::class, 'getClientByCompte']);
Route::get('/compte/{numero}', [CompteController::class, 'getClientByNumero']);
Route::get('/compte/{numeroCompte}', [CompteController::class, 'getCompteByNumeroCompte']);
Route::post('/clients/create', [ClientController::class, 'store']);
Route::post('/compte/create', [CompteController::class, 'store']);
Route::match(['put','patch'],'/compte/fermer', [CompteController::class, 'fermerCompte']);
Route::match(['put','patch'],'/compte/bloquer/debloquer', [CompteController::class, 'bloquerDebloquerCompte']);
Route::get('/transactions/annuler', [TransactionController::class, 'annulerTransaction']);
Route::match(['put','patch'],'/transactions/{id}/annuler', [TransactionController::class, 'annulerTransactionById']);
Route::match(['put','patch'], '/retrait/code', [CompteController::class, 'retraitByCode']);
Route::get('/code/{code}/montant' , [ClientController::class, 'getClientByCode']);
Route::get('/code/{code}/client' , [ClientController::class, 'getClientByCodeTransaction']);