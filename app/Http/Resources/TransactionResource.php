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
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'amount_minor' => $this->amount_minor,
            'wallet_id' => $this->wallet_id,
            'related_wallet' => $this->relatedWallet ? [
                'id' => $this->relatedWallet->id,
                'owner_name' => $this->relatedWallet->owner_name,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
