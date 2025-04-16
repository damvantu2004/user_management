<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailMail;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Mail::fake(); // Giả lập việc gửi email
    }

    /** @test */
    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/register', $userData);

        // Kiểm tra response
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'name',
                        'email',
                        'role',
                        'is_active',
                        'updated_at',
                        'created_at',
                        'id'
                    ]
                ]);

        // Kiểm tra user đã được tạo trong database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => 'user',
            'is_active' => false,
        ]);

        // Kiểm tra email xác thực đã được gửi
        Mail::assertSent(VerifyEmailMail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });

        // Kiểm tra token xác thực email đã được tạo
        $this->assertDatabaseHas('email_verification_tokens', [
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function test_user_cannot_register_with_existing_email()
    {
        // Tạo user có sẵn
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        Mail::assertNotSent(VerifyEmailMail::class);
    }

    /** @test */
    public function test_user_cannot_register_with_invalid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => '', // tên trống
            'email' => 'invalid-email', // email không hợp lệ
            'password' => 'short', // mật khẩu quá ngắn
            'password_confirmation' => 'different' // xác nhận mật khẩu không khớp
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);

        Mail::assertNotSent(VerifyEmailMail::class);
    }

    /** @test */
    public function test_user_can_verify_email_with_valid_token()
    {
        // Tạo user chưa xác thực
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => false,
            'email_verified_at' => null
        ]);

        // Tạo token xác thực
        $token = 'valid-token';
        \DB::table('email_verification_tokens')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now()
        ]);

        $response = $this->getJson("/api/verify-email?email={$user->email}&token={$token}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Xác thực email thành công! Bạn có thể đăng nhập.']);

        // Kiểm tra user đã được xác thực
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'is_active' => true,
        ]);
        $this->assertNotNull(User::find($user->id)->email_verified_at);

        // Kiểm tra token đã bị xóa
        $this->assertDatabaseMissing('email_verification_tokens', [
            'email' => $user->email
        ]);
    }

    /** @test */
    public function test_user_cannot_verify_email_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => false,
            'email_verified_at' => null
        ]);

        $response = $this->getJson("/api/verify-email?email={$user->email}&token=invalid-token");

        $response->assertStatus(400)
                ->assertJson(['error' => 'Token không hợp lệ hoặc đã hết hạn']);

        // Kiểm tra user vẫn chưa được xác thực
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'is_active' => false,
        ]);
    }

    // ... các test cases khác đã có trước đó ...
}