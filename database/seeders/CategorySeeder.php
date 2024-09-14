<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::create([
            'name' => 'Salary',
            'type' => 'Income',
            'icon' => 'salary.png',
        ]);

        Category::create([
            'name' => 'Food',
            'type' => 'Expense',
            'icon' => 'food.png',
        ]);
    }
}
