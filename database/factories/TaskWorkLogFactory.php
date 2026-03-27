<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskWorkLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id'               => Task::factory(),
            'task_type_id'          => null,
            'work_date'             => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'start_time'            => '07:00:00',
            'end_time'              => '15:00:00',
            'status'                => 'completed',
            'completed_tasks_count' => 0,
        ];
    }
}
