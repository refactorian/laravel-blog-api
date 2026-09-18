<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'disk' => 'public',
            'path' => 'media/'.fake()->uuid().'.jpg',
            'filename' => fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(1000, 1000000),
            'metadata' => null,
        ];
    }
}
