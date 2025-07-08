<?php

namespace Database\Factories;

use App\Models\Project;
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
        return [
            'project_id' => Project::factory(),
            'assigned_user_id' => User::factory(), // 👤 المستخدم الموكَّل
            'name' => 'test',
            'description' =>'test description',
            'status' => null,
            'priority' =>1,
            'due_date' => now()->addDays(rand(3, 15)),
        ];
    }
}
