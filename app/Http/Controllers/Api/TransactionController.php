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
    public function __construct(private TransactionService $transactionService) {}

    /**
     * Deposit money into a wallet
     * 
     * @param Wallet $wallet
     * @param DepositRequest $request
     * @return JsonResponse
     */
    public function deposit(Wallet $wallet, DepositRequest $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        $transaction = $this->transactionService->deposit($wallet, $request->amount, $idempotencyKey);
        return $this->respond(new TransactionResource($transaction));
    }

    /**
     * Withdraw money from a wallet
     * 
     * @param Wallet $wallet
     * @param WithdrawRequest $request
     * @return JsonResponse
     */
    public function withdraw(Wallet $wallet, WithdrawRequest $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');

        $transaction = $this->transactionService->withdraw($wallet, $request->amount, $idempotencyKey);
        return $this->respond(new TransactionResource($transaction));
    }

    /**
     * Transfer money between two wallets
     * 
     * @param TransferRequest $request
     * @return JsonResponse
     */
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

    /**
     * Get the transaction history for a wallet
     * 
     * @param Wallet $wallet
     * @param TransactionHistoryRequest $request
     * @return JsonResponse
     */
    public function history(Wallet $wallet, TransactionHistoryRequest $request): JsonResponse
    {
        $perPage = $request->get('per_page', config('pagination.per_page'));
        $filters = $request->only(['type', 'date_from', 'date_to']);

        $transactions = $this->transactionService->getTransactionsByWalletId($wallet->id, (int) $perPage, $filters);
        return $this->respond(new TransactionCollection($transactions));
    }
}
