<?php

namespace Database\Factories;

use App\Models\StatutoryDeductionTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatutoryDeductionTable>
 */
class StatutoryDeductionTableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['SSS', 'PHILHEALTH', 'PAGIBIG', 'TAX']),
            'effective_from' => now()->startOfYear()->toDateString(),
            'effective_to' => null,
            'rules' => ['brackets' => [['min' => 0, 'max' => null, 'employee_rate' => 0.02]]],
            'notes' => fake()->sentence(),
        ];
    }
}
