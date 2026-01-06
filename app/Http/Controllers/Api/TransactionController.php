<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\{
    DepositRequest,
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
}
