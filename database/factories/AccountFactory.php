<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'account_no' => $this->faker->unique()->numberBetween(112003,113000),
            'father_account_id' => 11,
            'statment_id' => 1,
            'balance' => $this->faker->numberBetween(1000,9000),
            'has_sons_acoounts'=>  0 ,
        ];
    }
}
