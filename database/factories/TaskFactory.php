<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title,
            'description' => fake()->sentence(15),
            'priority' => fake()->randomElement(array_column(TaskPriority::cases(), 'value')),
            'type' => fake()->randomElement(array_column(TaskType::cases(), 'value')),
            'due_date' => fake()->dateTimeBetween('+1 years', '+2 years'),
        ];
    }
}
