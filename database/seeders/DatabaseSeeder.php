<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo admin user cố định để test
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'), // override default password ở user factory
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 2. Tạo verified user cố định để test
        $verifiedUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 3. Tạo unverified user với token xác thực email
        $unverifiedUser = User::factory()->unverified()->create([
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Tạo token xác thực email cho unverified user
        DB::table('email_verification_tokens')->insert([
            'email' => 'unverified@example.com',
            'token' => 'test-verification-token',
            'created_at' => now(),
        ]);

        // 4. Tạo user với token reset password
        $resetPasswordUser = User::factory()->create([
            'name' => 'Reset Password User',
            'email' => 'reset@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Tạo token reset password
        DB::table('password_reset_tokens')->insert([
            'email' => 'reset@example.com',
            'token' => Hash::make('test-reset-token'),
            'created_at' => now(),
        ]);

        // 5. Tạo thêm users ngẫu nhiên để test
        // 5 users đã verify
        User::factory(5)->create();
        
        // 3 users chưa verify
        User::factory(3)->unverified()->create();
    }
}


