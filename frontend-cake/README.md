# FrontendCake

Dự án frontend được xây dựng bằng Angular CLI version 19.2.6.

## Yêu cầu hệ thống

- Node.js (version 18.x trở lên)
- npm (version 9.x trở lên)
- Angular CLI (version 19.x)

## Cài đặt

1. Clone dự án:
```bash
git clone https://github.com/your-username/frontend-cake.git
cd frontend-cake
```

2. Cài đặt dependencies:
```bash
npm install
```

## Chạy dự án

### Development server

Chạy server development:
```bash
ng serve
```
hoặc
```bash
npm start
```

Truy cập ứng dụng tại `http://localhost:4200`. Ứng dụng sẽ tự động reload khi có thay đổi trong source code.

### Build

Build dự án:
```bash
ng build
```

Các file build sẽ được lưu trong thư mục `dist/`. Sử dụng flag `--prod` cho production build.

## Cấu trúc dự án

```
frontend-cake/
├── src/
│   ├── app/
│   │   ├── components/     # Shared components
│   │   ├── pages/         # Page components
│   │   ├── services/      # Services
│   │   ├── guards/        # Route guards
│   │   ├── interceptors/  # HTTP interceptors
│   │   └── constants/     # Constants & configurations
│   ├── assets/           # Static assets
│   └── environments/     # Environment configurations
├── public/              # Public assets
└── ...
```

## Tính năng chính

- Xác thực người dùng (Login/Logout)
- Quản lý người dùng
- Dashboard admin
- Responsive design
- Bootstrap integration
- Font Awesome icons

## Công nghệ sử dụng

- Angular 19
- Bootstrap 5
- Font Awesome
- RxJS
- TypeScript

## API Backend

Ứng dụng kết nối với backend API tại `http://localhost:8000`. Đảm bảo backend server đang chạy trước khi khởi động frontend.

## Testing

### Unit Tests
```bash
ng test
```

### End-to-end Tests
```bash
ng e2e
```

## Contributing

1. Fork dự án
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## License

[MIT License](LICENSE)

## Liên hệ

Your Name - email@example.com

Project Link: https://github.com/your-username/frontend-cake
