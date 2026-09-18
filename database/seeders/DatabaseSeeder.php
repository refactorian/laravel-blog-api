<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create author users
        $authors = User::factory()->author()->count(5)->create();

        // Create regular users
        $users = User::factory()->count(10)->create();

        // Create categories
        $categories = Category::factory()->count(10)->create();

        // Create tags
        $tags = Tag::factory()->count(20)->create();

        // Create posts for authors
        $posts = collect();
        foreach ($authors as $author) {
            $posts = $posts->merge(
                Post::factory()->published()->count(5)->create([
                    'user_id' => $author->id,
                    'category_id' => $categories->random()->id,
                ])
            );
        }

        // Attach tags to posts
        foreach ($posts as $post) {
            $post->tags()->attach($tags->random(rand(1, 3)));
        }

        // Create comments
        foreach ($posts->random(min(20, $posts->count())) as $post) {
            $comment = Comment::factory()->create([
                'post_id' => $post->id,
                'user_id' => $users->random()->id,
            ]);

            // Create some replies
            if (rand(1, 2) === 1) {
                Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $users->random()->id,
                    'parent_id' => $comment->id,
                ]);
            }
        }

        // Create likes
        foreach ($posts->random(min(15, $posts->count())) as $post) {
            $post->likes()->create([
                'user_id' => $users->random()->id,
            ]);
        }

        // Create bookmarks
        foreach ($posts->random(min(10, $posts->count())) as $post) {
            $post->bookmarks()->create([
                'user_id' => $users->random()->id,
            ]);
        }

        // Create follows
        foreach ($users as $user) {
            $followCount = rand(1, 3);
            $authorsToFollow = $authors->random($followCount);
            foreach ($authorsToFollow as $authorToFollow) {
                if ($user->id !== $authorToFollow->id) {
                    Follow::create([
                        'follower_id' => $user->id,
                        'following_id' => $authorToFollow->id,
                    ]);
                }
            }
        }
    }
}
