<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type->value,
                    'amount_minor' => $item->amount_minor,
                    'wallet_id' => $item->wallet_id,
                    'related_wallet' => $item->relatedWallet ? [
                        'id' => $item->relatedWallet->id,
                        'owner_name' => $item->relatedWallet->owner_name,
                    ] : null,
                    'created_at' => $item->created_at,
                ];
            }),
            'pager' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
            ],
        ];
    }
}
