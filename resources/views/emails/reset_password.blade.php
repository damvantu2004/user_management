<p>Xin chào,</p>
<p>Bạn vừa yêu cầu đặt lại mật khẩu cho tài khoản của mình.</p>
<p>Vui lòng sử dụng mã token sau để đặt lại mật khẩu:</p>
<p><b>{{ $token }}</b></p>
<p>Hoặc truy cập đường dẫn (nếu có giao diện frontend):</p>
<p>
    <a href="{{ url('/reset-password?email=' . urlencode($email) . '&token=' . urlencode($token)) }}">
        Đặt lại mật khẩu
    </a>
</p>
<p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
<p>Trân trọng,<br>Đội ngũ hỗ trợ</p>