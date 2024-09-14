<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Goal;

class GoalSeeder extends Seeder
{
    public function run()
    {
        Goal::create([
            'user_id' => 1,
            'name' => 'Buy a Car',
            'target_amount' => 20000,
            'current_savings' => 5000,
            'deadline' => now()->addYear(),
        ]);
    }
}
