<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'related_wallet_id',
        'type',
        'amount_minor',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return ['type' => TransactionType::class];
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function relatedWallet()
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }
}
