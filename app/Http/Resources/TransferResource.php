<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class TransferResource
{
    private array $transactions;

    public function __construct(array $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'debit_transaction' => new TransactionResource($this->transactions['debit_transaction']),
            'credit_transaction' => new TransactionResource($this->transactions['credit_transaction']),
        ];
    }
}

