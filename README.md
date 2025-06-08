# User Management API

Dự án này là một API quản lý người dùng được xây dựng bằng Laravel, cung cấp các chức năng cơ bản như đăng ký, đăng nhập, quản lý thông tin người dùng và phân quyền.

## Tính năng

- Đăng ký tài khoản
- Đăng nhập và xác thực JWT
- Quản lý thông tin người dùng (xem, cập nhật, xóa)
- Phân quyền người dùng (admin, user)
- Đặt lại mật khẩu
- Tính năng "Nhớ mật khẩu"

## các bước thực hiện

## Mục lục
1. [Cài đặt môi trường](#1-cài-đặt-môi-trường)
   - Yêu cầu hệ thống
   - Cài đặt PHP, Composer, MySQL
   - Cài đặt các công cụ phát triển

2. [Khởi tạo dự án Laravel](#2-khởi-tạo-dự-án-laravel)
   - Tạo project
   - Cấu hình file .env
   - Cài đặt các package cần thiết

3. [Cấu hình cơ sở dữ liệu](#3-cấu-hình-cơ-sở-dữ-liệu)
   - Cấu hình kết nối MySQL
   - Tạo database
   

4. [Tạo migrations và models](#4-tạo-migrations-và-models)
   - Migration cho users table
   - Migration cho password_reset_tokens table
   - User model và các relationships

5. [Cài đặt và cấu hình JWT](#5-cài-đặt-và-cấu-hình-jwt)
   - Cài đặt jwt-auth package
   - Cấu hình JWT
   - Tích hợp JWT với User model

6. [Xây dựng các controllers](#6-xây-dựng-các-controllers)
   - AuthController (register, login, logout)
   - UserController (CRUD operations)
   - PasswordResetController

7. [Định nghĩa routes](#7-định-nghĩa-routes)
   - API routes cho authentication
   - API routes cho user management
   - API routes cho password reset
   - Route protection và middleware

8. [Tạo middlewares cho phân quyền](#8-tạo-middlewares-cho-phân-quyền)
   - JWT Authentication middleware
   - Admin Role middleware
   - Active User middleware

9. [Xây dựng chức năng quên mật khẩu](#9-xây-dựng-chức-năng-quên-mật-khẩu)
   - Gmail/Email configuration
   - Password reset tokens đã tạo trong migrations ở bước 4, tiếp tục triển khai 
   - Reset password flow 

10. [Kiểm thử API](#10-kiểm-thử-api)
    - Unit tests
    - Feature tests
    - API documentation với Postman/Swagger
    - Test cases và scenarios

11. [Triển khai ứng dụng](#11-triển-khai-ứng-dụng)
    - Chuẩn bị môi trường production
    - Security checklist
    - Deployment process
    - Monitoring và logging

12. [Thêm module Đặt lịch và Shared Services](#12-thêm-module-đặt-lịch-và-shared-services)
    - Tạo model và migration cho Appointment
    - Xây dựng AppointmentController
    - Tạo AppointmentService
    - Định nghĩa routes cho module đặt lịch
    - Sử dụng lại các Shared Services
    - Kiểm thử API đặt lịch
    - Seed dữ liệu mẫu

## API Endpoints

### Xác thực

- `POST /api/register` - Đăng ký tài khoản mới
- `POST /api/login` - Đăng nhập và nhận JWT token
- `POST /api/logout` - Đăng xuất (yêu cầu xác thực)
- `POST /api/refresh` - Làm mới JWT token (yêu cầu xác thực)
- `GET /api/me` - Lấy thông tin người dùng hiện tại (yêu cầu xác thực)

### Quản lý người dùng

- `GET /api/users` - Lấy danh sách người dùng (yêu cầu quyền admin)
- `GET /api/users/{id}` - Lấy thông tin chi tiết người dùng (yêu cầu xác thực)
- `PUT /api/users/{id}` - Cập nhật thông tin người dùng (yêu cầu xác thực)
- `DELETE /api/users/{id}` - Xóa người dùng (yêu cầu quyền admin)

### Quên mật khẩu

- `POST /api/password/forgot` - Gửi email đặt lại mật khẩu
- `POST /api/password/reset` - Đặt lại mật khẩu

### Đặt lịch

- `GET /api/appointments` - Lấy danh sách lịch hẹn (yêu cầu xác thực)
- `POST /api/appointments` - Tạo lịch hẹn mới (yêu cầu xác thực)
- `GET /api/appointments/{id}` - Xem chi tiết lịch hẹn (yêu cầu xác thực)
- `PUT /api/appointments/{id}` - Cập nhật lịch hẹn (yêu cầu xác thực)
- `DELETE /api/appointments/{id}` - Hủy lịch hẹn (yêu cầu xác thực)

## Cấu trúc dữ liệu

### Bảng users
- id (primary key)
- name (string)
- email (string, unique)
- password (string, hashed)
- phone (string, nullable)
- role (enum: 'admin', 'doctor', 'patient')
- is_active (boolean)
- remember_token (string, nullable)
- email_verified_at (timestamp)
- created_at, updated_at (timestamps)

### Bảng password_reset_tokens
- email (primary key)
- token (string)
- created_at (timestamp)

### Bảng email_verification_tokens
- email (primary key)
- token (string)
- created_at (timestamp)

### Bảng appointments
- id (primary key)
- patient_id (foreign key references patients)
- doctor_id (foreign key references doctors)
- appointment_date (date)
- appointment_time (time)
- status (enum: 'pending', 'confirmed', 'completed', 'cancelled')
- reason (text) - lý do khám
- notes (text, nullable) - ghi chú của bác sĩ
- created_at, updated_at

### Bảng doctors
- id (primary key)
- user_id (foreign key references users)
- specialty (string) - chuyên khoa
- qualification (string) - bằng cấp
- experience_years (integer)
- consultation_fee (decimal) - phí khám
- bio (text, nullable) - giới thiệu
- is_available (boolean) - có nhận bệnh không
- created_at, updated_at (timestamps)

### Bảng departments
- id (primary key)
- name (string) - tên khoa
- description (text, nullable)
- created_at, updated_at (timestamps)

### Bảng patients
- id (primary key)
- user_id (foreign key references users)
- date_of_birth (date)
- gender (enum: 'male', 'female', 'other')
- address (text)
- emergency_contact (string, nullable)
- created_at, updated_at

### Bảng doctor_schedules
- id (primary key)
- doctor_id (foreign key references doctors)
- day_of_week (integer) - 0=CN, 1=T2, ...
- start_time (time)
- end_time (time)
- is_available (boolean)
- created_at, updated_at

## Bảo mật

- Tất cả các API endpoints (ngoại trừ đăng ký, đăng nhập và đặt lại mật khẩu) đều yêu cầu JWT token hợp lệ
- Token có thời hạn 1 giờ và có thể được làm mới
- Mật khẩu được mã hóa bằng thuật toán bcrypt
## Giấy phép

Dự án này được phân phối dưới giấy phép MIT.
