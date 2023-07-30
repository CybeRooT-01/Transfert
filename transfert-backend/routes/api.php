<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompteController;

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
Route::get('/clients/{id}', [ClientController::class, 'show'] );
Route::get('/clients/{id}/compte',[ClientController::class,'getCompteByClient']);
// Route::post('/compte/{idCompte}/transaction', [CompteController::class, 'transaction']);
Route::post('/transaction', [CompteController::class, 'transaction']);


Route::get('/compte/{idCompte}/client', [CompteController::class, 'getClientByCompte']);