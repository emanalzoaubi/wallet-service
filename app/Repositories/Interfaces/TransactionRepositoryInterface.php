<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function findTransactionByWalletIdAndIdempotencyKey(int $walletId, string $idempotencyKey): ?Transaction;

    public function getTransactionsByWalletId(int $walletId, int $perPage, array $filters = []): LengthAwarePaginator;
}
