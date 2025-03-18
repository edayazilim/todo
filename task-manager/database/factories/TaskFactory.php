<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
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
        $statuses = ['pending', 'in_progress', 'completed'];
        
        $user = User::factory()->create();
        $creator = User::inRandomOrder()->first() ?? User::factory()->create();
        
        $status = $this->faker->randomElement($statuses);
        
        $startDate = match ($status) {
            'pending' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('+1 day', '+10 days') : null,
            'in_progress' => $this->faker->boolean(90) ? $this->faker->dateTimeBetween('-10 days', 'now') : null,
            'completed' => $this->faker->boolean(90) ? $this->faker->dateTimeBetween('-30 days', '-1 days') : null,
            default => null,
        };
        
        $endDate = null;
        if ($startDate) {
            $endDate = match ($status) {
                'pending' => $this->faker->dateTimeBetween($startDate, '+30 days'),
                'in_progress' => $this->faker->dateTimeBetween('+1 day', '+15 days'),
                'completed' => $this->faker->dateTimeBetween($startDate, 'now'),
                default => null,
            };
        }
        
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => $status,
            'user_id' => $user->id,
            'created_by' => $creator->id,
            'start_date' => $startDate ? $startDate->format('Y-m-d') : null,
            'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
        ];
    }
    
    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
    
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
    
    public function createdBy(User $creator): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $creator->id,
        ]);
    }
}
