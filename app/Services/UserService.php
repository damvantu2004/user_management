<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    /**
     * Get paginated list of users
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::query();

        // Apply filters if provided
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a user by ID
     *
     * @param int $id
     * @return User
     * @throws ModelNotFoundException
     */
    public function findUser(int $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // Model sẽ tự động hash
                'role' => $data['role'] ?? 'user',
                'is_active' => $data['is_active'] ?? true,
            ]);

            Log::info('New user created by admin', [
                'user_id' => $user->id,
                'created_by' => auth()->id()
            ]);

            return $user;
        });
    }

    /**
     * Update an existing user
     *
     * @param int $id
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function updateUser(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = $this->findUser($id);

            // Prevent changing email to one that's already taken
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $existingUser = User::where('email', $data['email'])->first();
                if ($existingUser) {
                    throw ValidationException::withMessages([
                        'email' => ['Email đã được sử dụng']
                    ]);
                }
            }

            // Prevent deactivating the last admin
            if (
                isset($data['is_active']) && 
                $data['is_active'] === false && 
                $user->role === 'admin' && 
                $user->is_active && 
                User::where('role', 'admin')->where('is_active', true)->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'is_active' => ['Không thể vô hiệu hóa admin cuối cùng']
                ]);
            }

            if (isset($data['password']) && !empty($data['password'])) {
                $user->password = $data['password']; // Sẽ được hash tự động bởi mutator
                unset($data['password']); // Loại bỏ password khỏi mảng data để tránh ghi đè
            }

            $user->update($data);

            Log::info('User updated by admin', [
                'user_id' => $user->id,
                'updated_by' => auth()->id(),
                'changes' => $data
            ]);

            return $user;
        });
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
    public function deleteUser(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $user = $this->findUser($id);

            // Prevent deleting the last admin
            if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
                throw ValidationException::withMessages([
                    'role' => ['Không thể xóa admin cuối cùng']
                ]);
            }

            $deleted = $user->delete();

            if ($deleted) {
                Log::info('User deleted by admin', [
                    'user_id' => $id,
                    'deleted_by' => auth()->id()
                ]);
            }

            return $deleted;
        });
    }

    /**
     * Toggle user's active status
     *
     * @param int $id
     * @return User
     * @throws ValidationException
     */
    public function toggleUserStatus(int $id): User
    {
        return DB::transaction(function () use ($id) {
            $user = $this->findUser($id);

            // Prevent deactivating the last admin
            if (
                $user->role === 'admin' &&
                $user->is_active &&
                User::where('role', 'admin')->where('is_active', true)->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'is_active' => ['Không thể vô hiệu hóa admin cuối cùng']
                ]);
            }

            $user->is_active = !$user->is_active;
            $user->save();

            Log::info('User status toggled by admin', [
                'user_id' => $user->id,
                'new_status' => $user->is_active,
                'toggled_by' => auth()->id()
            ]);

            return $user;
        });
    }
}


