<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Models\PerformanceReviewCycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceReview>
 */
class PerformanceReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'performance_review_cycle_id' => PerformanceReviewCycle::factory(),
            'employee_id' => Employee::factory(),
            'reviewer_id' => User::factory(),
            'status' => 'submitted',
            'overall_score' => 4.25,
            'summary' => fake()->sentence(),
            'comments' => fake()->paragraph(),
            'submitted_at' => now(),
        ];
    }
}
