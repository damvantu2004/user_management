<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực Email Thành Công</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f8fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            width: 90%;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        .logo {
            margin-bottom: 20px;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background-color: #48bb78;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 40px;
            font-weight: bold;
        }
        .header {
            color: #2d3748;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .message {
            color: #4a5568;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .login-button {
            display: inline-block;
            background-color: #4299e1;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .login-button:hover {
            background-color: #3182ce;
        }
        .footer {
            margin-top: 40px;
            color: #718096;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            
        </div>
        
        <div class="success-icon">X</div>
        
        <div class="header">
            Xác Thực Email Thất bại!
        </div>
        
        <div class="message">
            <p>Tài khoản của bạn chưa được xác thực thành công.</p>
        </div>
        
        <a href="{{ url('http://localhost:4200/auth') }}" class="login-button">
            Đăng Nhập Ngay
        </a>
        
        <div class="footer">
            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi tại <a href="mailto:damvantu2004@gmail.com">damvantu2004@gmail.com</a></p>
            <p>&copy; {{ date('Y') }} User Management. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>