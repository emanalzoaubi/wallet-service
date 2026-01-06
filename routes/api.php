<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;


Route::post('/wallets', [WalletController::class, 'store']);
Route::get('/wallets/{id}', [WalletController::class, 'show']);
Route::get('/wallets', [WalletController::class, 'index']);
Route::get('/wallets/{id}/balance', [WalletController::class, 'showBalance']);
Route::get('/wallets/{wallet}/transactions', [TransactionController::class, 'history']);

Route::middleware('idempotency')->group(function () {
    Route::post('/wallets/{wallet}/deposit', [TransactionController::class, 'deposit']);
    Route::post('/wallets/{wallet}/withdraw', [TransactionController::class, 'withdraw']);
    Route::post('/transfer', [TransactionController::class, 'transfer']);
});

Route::get('/health', fn() => response()->json(['status' => 'ok']));
