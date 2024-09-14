<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        Transaction::create([
            'user_id' => 1,
            'wallet_id' => 1,
            'category_id' => 1, // Salary
            'amount' => 2000,
            'transaction_type' => 'Income',
            'note' => 'Monthly salary',
            'transaction_date' => now(),
            'currency' => 'USD',
        ]);

        Transaction::create([
            'user_id' => 1,
            'wallet_id' => 1,
            'category_id' => 2, // Food
            'amount' => 50,
            'transaction_type' => 'Expense',
            'note' => 'Groceries',
            'transaction_date' => now(),
            'currency' => 'USD',
        ]);
    }
}
