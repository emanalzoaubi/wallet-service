<?php

namespace App\Http\Resources;

use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'type' => $this->type->value,
            'amount_minor' => $this->amount_minor,
            'wallet_id' => $this->wallet_id,
            'created_at' => $this->created_at,
        ];

        // Only include related_wallet for transfer transactions
        if ($this->isTransferTransaction()) {
            $data['related_wallet'] = [
                'id' => $this->relatedWallet->id,
                'owner_name' => $this->relatedWallet->owner_name,
            ];
        }

        return $data;
    }

    private function isTransferTransaction(): bool
    {
        return $this->type === TransactionType::TRANSFER_DEBIT || $this->type === TransactionType::TRANSFER_CREDIT;
    }
}

