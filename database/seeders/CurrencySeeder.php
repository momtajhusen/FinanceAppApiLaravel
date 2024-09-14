<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        Currency::create([
            'currency_code' => 'USD',
            'currency_name' => 'United States Dollar',
            'exchange_rate_to_base' => 1,
        ]);

        Currency::create([
            'currency_code' => 'INR',
            'currency_name' => 'Indian Rupee',
            'exchange_rate_to_base' => 0.012,
        ]);
    }
}
