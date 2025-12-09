<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'created_by' => null,
            'likes' => $this->faker->numberBetween(0, 100),
            'views' => $this->faker->numberBetween(10, 500),
        ];
    }
}
