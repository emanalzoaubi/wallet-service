<?php

namespace App\Services;

use App\Enums\TransactionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Exceptions\{
    CurrencyMismatchException,
    IdempotencyKeyViolationException,
    InsufficientFundsException,
    InvalidTransferException
};
use App\Models\{
    Transaction,
    Wallet
};
use App\Repositories\Interfaces\{
    TransactionRepositoryInterface,
    WalletRepositoryInterface
};

class TransactionService
{
    public function __construct(
       private WalletRepositoryInterface $walletRepository,
       private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Deposit money into a wallet
     * 
     * @param Wallet $wallet
     * @param int $amount
     * @param string $idempotencyKey
     * @return Transaction
     */
    public function deposit(Wallet $wallet, int $amount, string $idempotencyKey): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $idempotencyKey) {
            $walletId = $wallet->id;
            $wallet = $this->findAndLockWallet($walletId);

            // Check if the transaction already exists
            $existingTransaction = $this->findTransactionByWalletIdAndIdempotencyKey($walletId, $idempotencyKey);

            if ($existingTransaction) {
                return $existingTransaction;
            }

            // calculate the new wallet balance
            $newBalanceMinor = $wallet->balance_minor + $amount;

            // update the wallet balance
            $this->updateWalletBalanceMinor($walletId, $newBalanceMinor);

            // create the transaction
            return $this->createTransaction($walletId, TransactionType::DEPOSIT, $amount, $idempotencyKey);
        });
    }

    /**
     * Withdraw money from a wallet
     * 
     * @param Wallet $wallet
     * @param int $amount
     * @param string $idempotencyKey
     * @return Transaction
     */
    public function withdraw(Wallet $wallet, int $amount, string $idempotencyKey): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $idempotencyKey) {
            $walletId = $wallet->id;
            $wallet = $this->findAndLockWallet($walletId);

            // Check if the transaction already exists, if yes, return the existing transaction
            $existingTransaction = $this->findTransactionByWalletIdAndIdempotencyKey($walletId, $idempotencyKey);

            if ($existingTransaction) {
                return $existingTransaction;
            }


            $balanceMinor = $wallet->balance_minor;
            // Check if the wallet has enough balance, if not, throw an exception
            if ($balanceMinor < $amount) {
                $shortfall = $amount - $balanceMinor;
                throw new InsufficientFundsException("You need {$shortfall} more to complete this withdrawal");
            }

            // calculate the new wallet balance
            $newBalanceMinor = $balanceMinor - $amount;

            // update the wallet balance
            $this->updateWalletBalanceMinor($walletId, $newBalanceMinor);

            return $this->createTransaction($walletId, TransactionType::WITHDRAW, $amount, $idempotencyKey);
        });
    }

    /**
     * Transfer money between two wallets
     * 
     * @param int $fromWalletId
     * @param int $toWalletId
     * @param int $amount
     * @param string $idempotencyKey
     * @return array
     */
    public function transfer(int $fromWalletId, int $toWalletId, int $amount, string $idempotencyKey): array
    {
        // Check if the from wallet is the same as the to wallet, if yes, throw an exception
        if ($fromWalletId === $toWalletId) {
            throw new InvalidTransferException('Cannot transfer to the same wallet.');
        }

        return DB::transaction(function () use ($fromWalletId, $toWalletId, $amount, $idempotencyKey) {

            // get the wallets to lock based on the minimum and maximum ids, this is to avoid deadlocks
            $firstIdToLock = min($fromWalletId, $toWalletId);
            $secondIdToLock = max($fromWalletId, $toWalletId);

            // lock the wallets
            $this->findAndLockWallet($firstIdToLock);
            $this->findAndLockWallet($secondIdToLock);

            // get the wallets
            $fromWallet = $this->findWalletById($fromWalletId);
            $toWallet = $this->findWalletById($toWalletId);

            // Check if the from wallet and to wallet have the same currency, if not, throw an exception
            if ($fromWallet->currency !== $toWallet->currency) {
                throw new CurrencyMismatchException('Currency mismatch. Transfer must use the same currency.');
            }

            // Check if the debit and credit transactions already exist, if yes, return the existing transactions
            $existingDebitTransaction = $this->findTransactionByWalletIdAndIdempotencyKey($fromWalletId, $idempotencyKey);
            $existingCreditTransaction = $this->findTransactionByWalletIdAndIdempotencyKey($toWalletId, $idempotencyKey);

            if ($existingDebitTransaction && $existingCreditTransaction) {
                return [
                    'debit_transaction' => $existingDebitTransaction,
                    'credit_transaction' => $existingCreditTransaction,
                ];
            }

            // Check if the debit or credit transaction already exists, if yes, throw an exception
            if ($existingDebitTransaction || $existingCreditTransaction) {
                throw new IdempotencyKeyViolationException('Idempotency key violation. Please use a different idempotency key.');
            }

            // get the from wallet balance
            $fromWalletBalanceMinor = $fromWallet->balance_minor;

            // Check if the from wallet has enough balance, if not, throw an exception
            if ($fromWalletBalanceMinor < $amount) {
                throw new InsufficientFundsException('Insufficient balance for transfer');
            }

            $toWalletBalanceMinor = $toWallet->balance_minor;

            // update the from wallet balance
            $this->updateWalletBalanceMinor($fromWalletId, $fromWalletBalanceMinor - $amount);
            $this->updateWalletBalanceMinor($toWalletId, $toWalletBalanceMinor + $amount);

            // create the debit and credit transactions
            $debitTransaction = $this->createTransaction($fromWalletId, TransactionType::TRANSFER_DEBIT, $amount, $idempotencyKey, $toWalletId);
            $creditTransaction = $this->createTransaction($toWalletId, TransactionType::TRANSFER_CREDIT, $amount, $idempotencyKey, $fromWalletId);

            return [
                'debit_transaction' => $debitTransaction,
                'credit_transaction' => $creditTransaction,
            ];
        });
    }

    /**
     * Get the transactions by wallet id
     * 
     * @param int $walletId
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getTransactionsByWalletId(int $walletId, int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->transactionRepository->getTransactionsByWalletId($walletId, $perPage, $filters);
    }

    private function createTransaction(int $walletId, TransactionType $type, int $amount, string $idempotencyKey, ?int $relatedWalletId = null): Transaction
    {
        return $this->transactionRepository->create([
            'wallet_id' => $walletId,
            'type' => $type,
            'amount_minor' => $amount,
            'idempotency_key' => $idempotencyKey,
            'related_wallet_id' => $relatedWalletId,
        ]);
    }

    private function updateWalletBalanceMinor(int $walletId, int $newBalanceMinor): void
    {
        $this->walletRepository->update($walletId, ['balance_minor' => $newBalanceMinor]);
    }

    private function findTransactionByWalletIdAndIdempotencyKey(int $walletId, string $idempotencyKey): ?Transaction
    {
        return $this->transactionRepository->findTransactionByWalletIdAndIdempotencyKey($walletId, $idempotencyKey);
    }

    private function findAndLockWallet(int $walletId): Wallet
    {
        return $this->walletRepository->findByIdWithLock($walletId);
    }

    private function findWalletById(int $walletId): Wallet
    {
        return $this->walletRepository->findById($walletId);
    }
}
