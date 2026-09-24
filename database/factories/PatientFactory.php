<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'document' => fake()->numerify('###########'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('###########'),
            'birthday' => fake()->date(),
            'observations' => null,
        ];
    }
}
