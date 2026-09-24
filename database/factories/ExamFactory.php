<?php

namespace Database\Factories;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => strtoupper(fake()->bothify('EX-####')),
            'cost' => fake()->randomFloat(2, 5, 50),
            'price_sus' => fake()->randomFloat(2, 20, 100),
            'price_particular' => fake()->randomFloat(2, 30, 150),
        ];
    }
}
