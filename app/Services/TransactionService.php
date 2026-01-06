<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Exceptions\CurrencyMismatchException;
use App\Exceptions\IdempotencyKeyViolationException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransferException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\{
    TransactionRepositoryInterface,
    WalletRepositoryInterface
};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionService
{
    private $walletRepository;
    private $transactionRepository;

    public function __construct(WalletRepositoryInterface $walletRepository, TransactionRepositoryInterface $transactionRepository)
    {
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function deposit(Wallet $wallet, int $amount, string $idempotencyKey): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $idempotencyKey) {
            $walletId = $wallet->id;
            $wallet = $this->findAndLockWallet($walletId);

            $existingTransaction = $this->findTransactionByWalletIdAndIdempotencyKey($walletId, $idempotencyKey);

            if ($existingTransaction) {
                return $existingTransaction;
            }

            $newBalanceMinor = $wallet->balance_minor + $amount;

            $this->updateWalletBalanceMinor($walletId, $newBalanceMinor);

            return $this->createTransaction($walletId, TransactionType::DEPOSIT, $amount, $idempotencyKey);
        });
    }

    public function withdraw(Wallet $wallet, int $amount, string $idempotencyKey): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $idempotencyKey) {
            $walletId = $wallet->id;
            $wallet = $this->findAndLockWallet($walletId);

            $existingTransaction = $this->findTransactionByWalletIdAndIdempotencyKey($walletId, $idempotencyKey);

            if ($existingTransaction) {
                return $existingTransaction;
            }

            $balanceMinor = $wallet->balance_minor;

            if ($balanceMinor < $amount) {
                $shortfall = $amount - $balanceMinor;
                throw new InsufficientFundsException("You need {$shortfall} more to complete this withdrawal");
            }

            $newBalanceMinor = $balanceMinor - $amount;

            $this->updateWalletBalanceMinor($walletId, $newBalanceMinor);

            return $this->createTransaction($walletId, TransactionType::WITHDRAW, $amount, $idempotencyKey);
        });
    }

    public function transfer(int $fromWalletId, int $toWalletId, int $amount, string $idempotencyKey): array
    {
        if ($fromWalletId === $toWalletId) {
            throw new InvalidTransferException('Cannot transfer to the same wallet.');
        }

        return DB::transaction(function () use ($fromWalletId, $toWalletId, $amount, $idempotencyKey) {
            $firstIdToLock = min($fromWalletId, $toWalletId);
            $secondIdToLock = max($fromWalletId, $toWalletId);

            $this->findAndLockWallet($firstIdToLock);
            $this->findAndLockWallet($secondIdToLock);

            $fromWallet = $this->walletRepository->findById($fromWalletId);
            $toWallet = $this->walletRepository->findById($toWalletId);

            if ($fromWallet->currency !== $toWallet->currency) {
                throw new CurrencyMismatchException('Currency mismatch. Transfer must use the same currency.');
            }

            $existingDebitTransaction = $this->findTransactionByWalletIdAndIdempotencyKey($fromWalletId, $idempotencyKey);
            $existingCreditTransaction = $this->findTransactionByWalletIdAndIdempotencyKey($toWalletId, $idempotencyKey);

            if ($existingDebitTransaction && $existingCreditTransaction) {
                return [
                    'debit_transaction' => $existingDebitTransaction,
                    'credit_transaction' => $existingCreditTransaction,
                ];
            }

            if ($existingDebitTransaction || $existingCreditTransaction) {
                throw new IdempotencyKeyViolationException('Idempotency key violation. Please use a different idempotency key.');
            }

            $fromWalletBalanceMinor = $fromWallet->balance_minor;

            if ($fromWalletBalanceMinor < $amount) {
                throw new InsufficientFundsException('Insufficient balance for transfer');
            }

            $toWalletBalanceMinor = $toWallet->balance_minor;

            $this->updateWalletBalanceMinor($fromWalletId, $fromWalletBalanceMinor - $amount);
            $this->updateWalletBalanceMinor($toWalletId, $toWalletBalanceMinor + $amount);

            $debitTransaction = $this->createTransaction($fromWalletId, TransactionType::TRANSFER_DEBIT, $amount, $idempotencyKey, $toWalletId);
            $creditTransaction = $this->createTransaction($toWalletId, TransactionType::TRANSFER_CREDIT, $amount, $idempotencyKey, $fromWalletId);

            return [
                'debit_transaction' => $debitTransaction,
                'credit_transaction' => $creditTransaction,
            ];
        });
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
}
