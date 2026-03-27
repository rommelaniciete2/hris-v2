<?php

namespace Database\Factories;

use App\Models\AttendanceLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceLocation>
 */
class AttendanceLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Site',
            'code' => fake()->unique()->lexify('LOC-???'),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(14.5, 14.7),
            'longitude' => fake()->longitude(120.9, 121.1),
            'radius_meters' => 100,
            'is_active' => true,
        ];
    }
}
