<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CostCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\CostCenter::factory(500)->create();
        \App\Models\Account::factory(700)->create();
        // \App\Models\User::factory()->count(1000)->create([
            // 'name' => 'Test User',
            // 'email' => 'test@example.com',
        // ]);
    }
}
