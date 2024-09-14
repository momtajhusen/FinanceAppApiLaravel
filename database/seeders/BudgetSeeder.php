<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;

class BudgetSeeder extends Seeder
{
    public function run()
    {
        Budget::create([
            'user_id' => 1,
            'category_id' => 2, // Food
            'amount' => 500,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
        ]);
    }
}
