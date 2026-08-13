<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'outlet_id' => Outlet::factory(),
            'merchant_id' => fn (array $attributes): int => (int) Outlet::query()->findOrFail($attributes['outlet_id'])->merchant_id,
            'bill_total' => fake()->randomFloat(2, 1000, 10000),
            'created_by' => fn (array $attributes): int => (int) Outlet::query()->findOrFail($attributes['outlet_id'])->created_by,
            'updated_by' => fn (array $attributes): int => (int) Outlet::query()->findOrFail($attributes['outlet_id'])->updated_by,
        ];
    }
}
