<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser()
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_admin_can_list_users()
    {
        $admin = $this->createAdminUser();
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'role',
                            'is_active'
                        ]
                    ],
                    'total'
                ]
            ]);
    }

    /** @test */
    public function test_admin_can_get_user_details()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
            ->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
    }

    /** @test */
    public function test_admin_can_update_user()
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'is_active' => false
        ];

        $response = $this->actingAs($admin)
            ->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Cập nhật thông tin người dùng thành công'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'is_active' => false
        ]);
    }

    /** @test */
    public function test_normal_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->getJson('/api/users');

        $response->assertStatus(403);
    }
}