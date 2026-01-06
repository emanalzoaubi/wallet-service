<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\{
    DepositRequest,
    TransactionHistoryRequest,
    TransferRequest,
    WithdrawRequest
};
use App\Http\Resources\{
    TransactionCollection,
    TransactionResource,
    TransferResource
};
use App\Models\Wallet;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;

class TransactionController extends BaseController
{
    private $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function deposit(Wallet $wallet, DepositRequest $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        $transaction = $this->transactionService->deposit($wallet, $request->amount, $idempotencyKey);
        return $this->respond(new TransactionResource($transaction));
    }

    public function withdraw(Wallet $wallet, WithdrawRequest $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        $transaction = $this->transactionService->withdraw($wallet, $request->amount, $idempotencyKey);
        return $this->respond(new TransactionResource($transaction));
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');
        $fromWalletId = $request->from_wallet_id;
        $toWalletId = $request->to_wallet_id;
        $amount = $request->amount;

        $transactions = $this->transactionService->transfer($fromWalletId, $toWalletId, $amount, $idempotencyKey);
        $transferResource = new TransferResource($transactions);
        return $this->respond($transferResource->toArray($request));
    }
    
    public function history(Wallet $wallet, TransactionHistoryRequest $request): JsonResponse
    {
        $perPage = $request->get('per_page', config('pagination.per_page'));
        $filters = $request->only(['type', 'date_from', 'date_to']);

        $transactions = $this->transactionService->getTransactionsByWalletId($wallet->id, (int) $perPage, $filters);
        return $this->respond(new TransactionCollection($transactions));
    }
}
