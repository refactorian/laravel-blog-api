<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingHistoryFactory extends Factory
{
    protected $model = ReadingHistory::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'read_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
