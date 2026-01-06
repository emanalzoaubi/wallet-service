<?php

namespace App\Services;

use App\Models\Wallet;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletService
{
    private $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    /**
     * Get all wallets
     * 
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllWallets(int $perPage, array $filters = []): LengthAwarePaginator
    {
        // Convert simple filters to proper format
        $processedFilters = [];
        
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            // For owner_name, use LIKE for partial matching
            if ($key === 'owner_name') {
                $processedFilters[$key] = ['like' => $value];
            } else {
                // For other fields like currency, use exact match
                $processedFilters[$key] = $value;
            }
        }
        
        return $this->walletRepository->all($perPage, $processedFilters);
    }

    /**
     * Create a new wallet
     * 
     * @param string $ownerName
     * @param string $currency
     * @return Wallet
     */
    public function createWallet(string $ownerName, string $currency): Wallet
    {
        return $this->walletRepository->create([
            'owner_name' => $ownerName,
            'currency' => strtoupper($currency),
        ]);
    }

    /**
     * Get a wallet by id
     * 
     * @param int $id
     * @return Wallet
     */
    public function getWalletById(int $id): Wallet
    {
        return $this->walletRepository->findById($id);
    }

    /**
     * Get the balance of a wallet
     * 
     * @param int $id
     * @return Wallet
     */
    public function getWalletBalance(int $id): Wallet
    {
        return $this->walletRepository->findById($id, ['balance_minor']);
    }
}
