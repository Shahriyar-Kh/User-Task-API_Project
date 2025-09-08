<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = auth('api')->login($user); // Fixed: use 'api' guard

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/admin/users')
             ->assertForbidden();
    }

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = auth('api')->login($admin); // Fixed: use 'api' guard

        $this->withHeader('Authorization', "Bearer $token")
             ->getJson('/api/admin/users')
             ->assertOk()
             ->assertJsonStructure(['data']); // pagination
    }
}