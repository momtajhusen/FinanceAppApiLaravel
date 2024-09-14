<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bill;

class BillSeeder extends Seeder
{
    public function run()
    {
        Bill::create([
            'user_id' => 1,
            'name' => 'Electricity Bill',
            'amount' => 100,
            'due_date' => now()->addDays(5),
            'frequency' => 'Monthly',
            'is_paid' => false,
        ]);
    }
}
