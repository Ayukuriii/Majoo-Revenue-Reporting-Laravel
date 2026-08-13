<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Merchant>
 */
class MerchantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'merchant_name' => fake()->unique()->lexify('Merchant ????'),
            'created_by' => fn (array $attributes): int => (int) $attributes['user_id'],
            'updated_by' => fn (array $attributes): int => (int) $attributes['user_id'],
        ];
    }
}
