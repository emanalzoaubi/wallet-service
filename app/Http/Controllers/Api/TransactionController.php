<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\{
    DepositRequest,
    TransferRequest,
    WithdrawRequest
};
use App\Models\Wallet;
use App\Services\TransactionService;

class TransactionController extends BaseController
{
    private $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function deposit(Wallet $wallet, DepositRequest $request)
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        $transaction = $this->transactionService->deposit($wallet, $request->amount, $idempotencyKey);
        return $this->respond($transaction);
    }

    public function withdraw(Wallet $wallet, WithdrawRequest $request)
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        $transaction = $this->transactionService->withdraw($wallet, $request->amount, $idempotencyKey);
        return $this->respond($transaction);
    }

    public function transfer(TransferRequest $request)
    {
        $idempotencyKey = $request->header('Idempotency-Key');
        $fromWalletId = $request->from_wallet_id;
        $toWalletId = $request->to_wallet_id;
        $amount = $request->amount;

        $transactions = $this->transactionService->transfer($fromWalletId, $toWalletId, $amount, $idempotencyKey);
        return $this->respond($transactions);
    }

}
