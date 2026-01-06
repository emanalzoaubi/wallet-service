<?php

namespace App\Repositories\Interfaces;

use App\Models\Wallet;

interface WalletRepositoryInterface extends BaseRepositoryInterface
{
    public function findByIdWithLock(int $walletId): Wallet;
}