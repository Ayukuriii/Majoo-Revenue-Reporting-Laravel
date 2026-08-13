<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Merchant 1',
            'email' => 'merchant1@example.com',
        ]);

        User::factory()->create([
            'name' => 'Merchant 2',
            'email' => 'merchant2@example.com',
        ]);

        $this->call(AssignmentDataSeeder::class);
    }
}
