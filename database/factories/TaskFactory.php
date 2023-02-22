<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory {

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
    	return [
    	    'title' => $this->faker->title,
            'description' => $this->faker->paragraph,
            'status' => $this->faker->text,
            'created_by' => $this->faker->name,
            'assign_to' => $this->faker->name,
    	];
    }
}
