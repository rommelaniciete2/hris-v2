<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interview>
 */
class InterviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'interviewer_id' => User::factory(),
            'scheduled_at' => now()->addDays(3),
            'status' => 'scheduled',
            'notes' => fake()->sentence(),
        ];
    }
}
