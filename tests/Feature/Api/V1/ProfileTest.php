<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile', [
                'name' => 'Updated Name',
                'bio' => 'Updated bio.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'bio' => 'Updated bio.',
        ]);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/v1/profile', [
            'name' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_change_password(): void
    {
        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'old',
            'password' => 'new',
            'password_confirmation' => 'new',
        ]);

        $response->assertStatus(401);
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'old-password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_profile_name_is_optional_on_update(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile', [
                'bio' => 'Updated bio only.',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Original Name',
            'bio' => 'Updated bio only.',
        ]);
    }
}
