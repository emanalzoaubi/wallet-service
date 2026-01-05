<?php

namespace App\Services;

use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletService
{
    private $walletRepository;
    
    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function getAllWallets(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->walletRepository->all($perPage, $filters);
    }

    public function createWallet(string $ownerName, string $currency): Wallet
    {
        return $this->walletRepository->create([
            'owner_name' => $ownerName,
            'currency' => strtoupper($currency),
        ]);
    }

    public function getWalletById(int $id): Wallet
    {
        return $this->walletRepository->findById($id);
    }
}