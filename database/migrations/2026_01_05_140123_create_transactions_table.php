<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('related_wallet_id')->nullable()->constrained('wallets')->cascadeOnDelete();
            $table->string('type');
            $table->bigInteger('amount_minor');
            $table->string('idempotency_key', 250);

            $table->unique(['wallet_id','idempotency_key'], 'w_idemp_uk');
            $table->index(['wallet_id', 'type', 'created_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
