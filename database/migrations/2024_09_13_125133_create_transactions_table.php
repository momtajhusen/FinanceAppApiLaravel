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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained('wallets'); // Updated constraint
            $table->foreignId('category_id')->constrained('categories'); // Optional, depends on your needs
            $table->decimal('amount', 10, 2);
            $table->enum('transaction_type', ['Income', 'Expense']);
            $table->text('note')->nullable();
            $table->date('transaction_date');
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate_to_base', 10, 6);
            $table->string('attachment_url')->nullable();
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
