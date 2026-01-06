<?php

namespace App\Repositories;

use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Models\Wallet;

class WalletRepository extends BaseRepository implements WalletRepositoryInterface
{
    public function __construct(Wallet $model)
    {
        parent::__construct($model);
    }

    public function findByIdWithLock(int $walletId): Wallet
    {
        return Wallet::lockForUpdate()->findOrFail($walletId);
    }
}