<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function user_can_register()
    {
        $payload = [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->postJson('/api/auth/register', $payload)
             ->assertCreated()
             ->assertJsonStructure(['user', 'token']); // Fixed: should be 'token', not 'access_token'
    }

    /** @test */
    public function user_can_login_and_get_token()
    {
        $user = User::factory()->create([
            'password' => 'password123' // Let the User model handle hashing
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123'
        ])->assertOk()
          ->assertJsonStructure(['token']); // Fixed: should be 'token', not 'access_token'
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/auth/logout')
             ->assertOk()
             ->assertJson(['message' => 'logged_out']); // Fixed: match controller response
    }

    /** @test */
    public function user_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/auth/refresh')
             ->assertOk()
             ->assertJsonStructure(['token']); // Fixed: should be 'token', not 'access_token'
    }
}