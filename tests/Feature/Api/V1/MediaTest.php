<?php

namespace Tests\Feature\Api\V1;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_media(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/media', [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Media uploaded successfully.',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'filename', 'mime_type', 'size', 'url'],
            ]);

        $this->assertDatabaseHas('media', [
            'user_id' => $user->id,
            'mime_type' => 'image/jpeg',
        ]);
    }

    public function test_unauthenticated_user_cannot_upload_media(): void
    {
        $response = $this->postJson('/api/v1/media', [
            'file' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_upload_without_file(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/media', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_user_cannot_upload_invalid_file_type(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/media', [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_owner_can_delete_media(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $media = Media::factory()->create(['user_id' => $user->id]);

        Storage::fake('public');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/media/{$media->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_non_owner_cannot_delete_media(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;
        $media = Media::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/media/{$media->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_media(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('auth-token')->plainTextToken;
        $media = Media::factory()->create();

        Storage::fake('public');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/v1/media/{$media->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_unauthenticated_user_cannot_delete_media(): void
    {
        $media = Media::factory()->create();

        $response = $this->deleteJson("/api/v1/media/{$media->id}");

        $response->assertStatus(401);
    }
}
