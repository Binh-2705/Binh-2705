<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="{{ asset('public/style1.css') }}?v=20260410-1">
    <link rel="stylesheet" href="{{ asset('public/css/legacy-bridge.css') }}?v=20260410-1">
</head>
<body>
    <main class="login-shell">
    <section class="login-container login-compact">
        <div class="login-center">
            <span class="auth-title-badge">Khôi phục bằng token</span>
            <h2>Đặt lại mật khẩu</h2>
            <p>Token hợp lệ sẽ cho phép cập nhật mật khẩu trực tiếp mà không cần quay lại quy trình cũ.</p>
            @if ($errors->any())<div class="auth-alert auth-alert-error">{{ $errors->first() }}</div>@endif
            <form method="post" action="{{ route('password.reset.submit') }}" class="auth-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="MatKhauMoi">Mật khẩu mới</label>
                    <input id="MatKhauMoi" name="MatKhauMoi" type="password" required>
                </div>
                <div>
                    <label for="XacNhanMatKhau">Xác nhận mật khẩu</label>
                    <input id="XacNhanMatKhau" name="XacNhanMatKhau" type="password" required>
                </div>
                <button class="btn" type="submit">Cập nhật mật khẩu</button>
            </form>
        </div>
    </section>
    </main>
</body>
</html>