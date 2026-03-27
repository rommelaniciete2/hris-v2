<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Vacation Leave', 'Sick Leave']),
            'code' => fake()->unique()->lexify('L??'),
            'color' => fake()->hexColor(),
            'requires_balance' => true,
            'default_days' => fake()->randomFloat(2, 5, 15),
        ];
    }
}
