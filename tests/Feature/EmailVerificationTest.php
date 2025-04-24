<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_can_verify_email()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => false,
            'email_verified_at' => null
        ]);

        $token = 'valid-token';
        DB::table('email_verification_tokens')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now()
        ]);

        $response = $this->getJson("/api/verify-email?email={$user->email}&token={$token}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Xác thực email thành công! Bạn có thể đăng nhập.'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_invalid_verification_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => false
        ]);

        $response = $this->getJson("/api/verify-email?email={$user->email}&token=invalid-token");

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Token không hợp lệ hoặc đã hết hạn'
            ]);
    }
}