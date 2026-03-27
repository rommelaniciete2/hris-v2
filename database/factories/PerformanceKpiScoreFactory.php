<?php

namespace Database\Factories;

use App\Models\PerformanceKpiScore;
use App\Models\PerformanceReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceKpiScore>
 */
class PerformanceKpiScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'performance_review_id' => PerformanceReview::factory(),
            'name' => fake()->randomElement(['Quality', 'Teamwork', 'Ownership']),
            'score' => fake()->numberBetween(1, 5),
            'comments' => fake()->sentence(),
        ];
    }
}
