<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/wallets/{id}', [WalletController::class, 'show']);
    Route::get('/wallets', [WalletController::class, 'index']);
    Route::get('/wallets/{id}/balance', [WalletController::class, 'showBalance']);
    Route::get('/wallets/{wallet}/transactions', [TransactionController::class, 'history']);
});

Route::middleware('throttle:30,1')->group(function () {
    Route::post('/wallets', [WalletController::class, 'store']);
});

Route::middleware(['idempotency', 'throttle:20,1'])->group(function () {
    Route::post('/wallets/{wallet}/deposit', [TransactionController::class, 'deposit']);
    Route::post('/wallets/{wallet}/withdraw', [TransactionController::class, 'withdraw']);
    Route::post('/transfers', [TransactionController::class, 'transfer']);
});

Route::get('/health', fn() => response()->json(['status' => 'ok']));
