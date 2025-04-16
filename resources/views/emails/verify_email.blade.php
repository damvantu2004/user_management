<p>Xin chào,</p>
<p>Bạn vừa đăng ký tài khoản tại hệ thống của chúng tôi.</p>
<p>Vui lòng nhấn vào liên kết dưới đây để xác thực email và kích hoạt tài khoản:</p>
<p>
    <a href="{{ url('/api/verify-email?email=' . urlencode($email) . '&token=' . urlencode($token)) }}">
        Xác thực email
    </a>
</p>
<p>Nếu bạn không thực hiện đăng ký, vui lòng bỏ qua email này.</p>
<p>Trân trọng,<br>Đội ngũ hỗ trợ</p>