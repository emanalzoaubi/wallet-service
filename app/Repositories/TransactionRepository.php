<?php

namespace App\Repositories;


use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    public function findTransactionByWalletIdAndIdempotencyKey(int $walletId, string $idempotencyKey): ?Transaction
    {
        return Transaction::where('wallet_id', $walletId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }
}
