<?php

namespace App\Repositories;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function getTransactionsByWalletId(int $walletId, int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = Transaction::with(['wallet', 'relatedWallet'])
            ->where('wallet_id', $walletId)
            ->orderBy('created_at', 'desc');

        if (isset($filters['type']) && $filters['type']) {
            $query->where('type', TransactionType::from($filters['type']));
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }
}
