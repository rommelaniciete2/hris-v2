<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'documentable_type' => Employee::class,
            'documentable_id' => Employee::factory(),
            'category' => 'contract',
            'name' => fake()->word(),
            'original_name' => fake()->word().'.pdf',
            'path' => 'employees/sample.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'uploaded_by' => User::factory(),
        ];
    }
}
