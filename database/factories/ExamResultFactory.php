<?php

namespace Database\Factories;

use App\Models\ExamResult;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamResult>
 */
class ExamResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'patient_id' => fn (array $attributes) => Order::find($attributes['order_id'])?->patient_id,
            'file_disk' => 'local',
            'file_path' => 'exam-results/'.fake()->uuid().'.pdf',
            'original_filename' => 'resultado.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(10_000, 500_000),
            'file_hash' => hash('sha256', fake()->uuid()),
            'released_at' => null,
        ];
    }

    public function released(): static
    {
        return $this->state(fn () => ['released_at' => now()->subMinute()]);
    }
}
