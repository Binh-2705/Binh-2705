<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dang nhap - HRM Laravel</title>
    <style>
        :root {
            --bg: #f3f0e8;
            --card: #fffdf7;
            --ink: #1d252a;
            --accent: #0f6d5a;
            --accent-2: #d8a52f;
            --danger: #a91d3a;
            --line: #d7d0c1;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 20%, #f6dbc6 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, #d3e9dc 0%, transparent 35%),
                var(--bg);
            display: grid;
            place-items: center;
            padding: 16px;
        }
        .card {
            width: min(420px, 100%);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 12px 34px rgba(0, 0, 0, 0.08);
        }
        h1 {
            margin: 0 0 12px;
            font-size: 24px;
        }
        p { margin: 0 0 18px; color: #4d5559; }
        .err {
            margin: 0 0 14px;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #f0bec9;
            background: #fff2f5;
            color: var(--danger);
        }
        label { display: block; font-size: 14px; margin: 10px 0 6px; }
        input {
            width: 100%;
            height: 42px;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 0 12px;
            background: #fff;
        }
        button {
            width: 100%;
            margin-top: 14px;
            height: 44px;
            border: 0;
            border-radius: 10px;
            color: #fff;
            background: linear-gradient(120deg, var(--accent), #167f68);
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Dang nhap he thong</h1>
        <p>Ban Laravel migration cho du an quan ly nhan su.</p>

        @if ($errors->any())
            <div class="err">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('login.submit') }}">
            @csrf
            <label for="TenDangNhap">Ten dang nhap</label>
            <input id="TenDangNhap" name="TenDangNhap" value="{{ old('TenDangNhap') }}" required>

            <label for="MatKhau">Mat khau</label>
            <input id="MatKhau" name="MatKhau" type="password" required>

            <button type="submit">Dang nhap</button>
        </form>
    </main>
</body>
</html>
