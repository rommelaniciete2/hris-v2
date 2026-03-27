<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\JobPosting;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'position_id' => Position::factory(),
            'created_by' => User::factory(),
            'title' => fake()->jobTitle(),
            'status' => 'open',
            'description' => fake()->paragraph(),
            'closes_at' => now()->addMonth()->toDateString(),
        ];
    }
}
