<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_factory_creates_record()
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    public function test_password_is_hashed_if_model_mutator_exists()
    {
        // If your model/factory hashes passwords, assert that Hash::check returns true.
        $raw = 'password123';
        $user = User::factory()->create(['password' => $raw]); // no bcrypt
        $this->assertTrue(Hash::check($raw, $user->password));
    }
}
