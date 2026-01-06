<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;

interface TransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function findTransactionByWalletIdAndIdempotencyKey(int $walletId, string $idempotencyKey): ?Transaction;

}
