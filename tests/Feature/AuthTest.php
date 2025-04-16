<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);

        Mail::assertNotSent(VerifyEmailMail::class);
    }

    /** @test */
    public function test_user_can_verify_email_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => false,
            'email_verified_at' => null
        ]);

        $token = 'valid-token';
        \DB::table('email_verification_tokens')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now()
        ]);

        $response = $this->getJson("/api/verify-email?email={$user->email}&token={$token}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Xác thực email thành công! Bạn có thể đăng nhập.']);

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'is_active' => true,
        ]);
        $this->assertNotNull(User::find($user->id)->email_verified_at);

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

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function test_user_can_request_password_reset_with_valid_email()
    {
        Mail::fake();
        $user = User::factory()->create([
            'email' => 'forgot@example.com',
        ]);

        $response = $this->postJson('/api/password/forgot', [
            'email' => 'forgot@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Đã gửi email đặt lại mật khẩu!']);

        // Mail::assertSent(ResetPasswordMail::class, function ($mail) use ($user) {
        //     return $mail->hasTo($user->email);
        // });

        // Sử dụng đúng bảng password_reset_tokens
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    /** @test */
    public function test_user_cannot_request_password_reset_with_invalid_email()
    {
        $response = $this->postJson('/api/password/forgot', [
            'email' => 'not-exist@example.com',
        ]);

        // Sửa lại expect status 422 thay vì 200 vì email không tồn tại
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'not-exist@example.com',
        ]);
    }

    /** @test */
    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com'
        ]);

        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        $response = $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Sửa lại message cho khớp với controller
        $response->assertStatus(200)
            ->assertJson(['message' => 'Đặt lại mật khẩu thành công!']);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function test_user_cannot_reset_password_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'reset2@example.com'
        ]);

        // Không tạo token hợp lệ

        $response = $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Token không hợp lệ hoặc đã hết hạn']);

        $user->refresh();
        $this->assertFalse(\Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function test_user_cannot_reset_password_with_invalid_new_password()
    {
        $user = User::factory()->create([
            'email' => 'reset3@example.com'
        ]);

        $token = 'valid-reset-token-2';
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => \Hash::make($token),
            'created_at' => now()
        ]);

        // Sửa lại đường dẫn cho đúng
        $response = $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'short',  // Mật khẩu quá ngắn
            'password_confirmation' => 'notmatch'  // Không khớp với password
        ]);

        // Kỳ vọng nhận được lỗi validation
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}





