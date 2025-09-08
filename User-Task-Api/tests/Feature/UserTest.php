<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function authenticated_user_can_view_profile()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/users/me')
             ->assertOk()
             ->assertJsonPath('email', $user->email);
    }

    /** @test */
    public function authenticated_user_can_update_profile()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $payload = ['name' => 'Updated Name'];

        $this->withHeader('Authorization', "Bearer $token")
             ->putJson('/api/users/me', $payload)
             ->assertOk()
             ->assertJsonPath('name', 'Updated Name');
    }
}