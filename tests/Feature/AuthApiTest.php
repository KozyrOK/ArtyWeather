<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_api_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Ada',
            'second_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'locale' => 'en',
            'theme' => 'system',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'first_name', 'second_name', 'email', 'locale', 'theme'],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'first_name' => 'Ada',
            'second_name' => 'Lovelace',
            'locale' => 'en',
            'theme' => 'system',
        ]);
    }

    public function test_user_can_login_and_fetch_authenticated_profile(): void
    {
        User::factory()->create([
            'email' => 'grace@example.com',
            'password' => 'password',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'grace@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse
            ->assertOk()
            ->assertJsonStructure(['user', 'token'])
            ->json('token');

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'grace@example.com');
    }

    public function test_authenticated_user_can_logout_current_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/auth/logout')->assertNoContent();
    }
}