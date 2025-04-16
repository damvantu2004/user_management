<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Model User đại diện cho bảng 'users' trong cơ sở dữ liệu.
 * Chứa các thuộc tính, phương thức phục vụ xác thực, phân quyền, và các thao tác liên quan đến người dùng.
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Các trường có thể gán giá trị hàng loạt (mass assignment).
     * Giúp bảo vệ ứng dụng khỏi lỗ hổng Mass Assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',         // Tên người dùng
        'email',        // Email (duy nhất)
        'password',     // Mật khẩu (được hash tự động)
        'role',         // Vai trò: 'admin' hoặc 'user'
        'is_active',    // Trạng thái hoạt động (true/false)
    ];

    /**
     * Các trường sẽ bị ẩn khi trả về dữ liệu (ví dụ khi trả về JSON cho client).
     * Đảm bảo không lộ thông tin nhạy cảm.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',         // Ẩn mật khẩu
        'remember_token',   // Ẩn token "nhớ đăng nhập"
    ];

    /**
     * Ép kiểu cho các trường dữ liệu khi truy xuất hoặc lưu vào database.
     * - email_verified_at: chuyển thành đối tượng Carbon (datetime)
     * - password: tự động hash khi set giá trị mới
     * - is_active: chuyển thành kiểu boolean
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Lấy định danh (ID) của user để lưu vào claim 'sub' trong JWT.
     * Đây là giá trị duy nhất đại diện cho user trong hệ thống.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        // Trả về khóa chính (id) của user
        return $this->getKey();
    }

    /**
     * Trả về các custom claims (thông tin bổ sung) sẽ được thêm vào JWT.
     * Nếu không cần bổ sung gì, trả về mảng rỗng.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // Không thêm custom claim nào, trả về mảng rỗng
        return [];
    }

    /**
     * Kiểm tra xem user có phải là admin không.
     * Dùng cho middleware hoặc kiểm tra phân quyền trong controller.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra trạng thái hoạt động của user.
     * Nếu user bị khóa (is_active = false), có thể chặn truy cập hệ thống.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * (Có thể mở rộng) Định nghĩa các mối quan hệ với model khác, ví dụ:
     * - public function posts() { ... }
     * - public function passwordResetTokens() { ... }
     */
}