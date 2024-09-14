<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wallet;

class WalletSeeder extends Seeder
{
    public function run()
    {
        Wallet::create([
            'user_id' => 1,
            'name' => 'Main Wallet',
            'balance' => 1000,
            'currency' => 'USD',
        ]);

        Wallet::create([
            'user_id' => 2,
            'name' => 'Savings',
            'balance' => 5000,
            'currency' => 'INR',
        ]);
    }
}
