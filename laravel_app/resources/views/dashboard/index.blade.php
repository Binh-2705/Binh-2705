<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - HRM Laravel</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: #f7f8fa;
            color: #1d252a;
        }
        .wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 28px 16px;
        }
        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .card {
            background: #fff;
            border: 1px solid #e3e6ec;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
        }
        .pill {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef2f7;
            margin: 0 8px 8px 0;
            font-size: 13px;
        }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            color: #fff;
            background: #0f6d5a;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <div>
                <h1 style="margin:0 0 8px;">Laravel migration dashboard</h1>
                <div>Tai khoan: <strong>{{ $taiKhoan['TenDangNhap'] ?? 'N/A' }}</strong></div>
            </div>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="btn" type="submit">Dang xuat</button>
            </form>
        </div>

        <section class="card">
            <h3 style="margin:0 0 10px;">Quyen hien tai</h3>
            @forelse ($quyen as $item)
                <span class="pill">{{ $item }}</span>
            @empty
                <div>Khong co quyen nao.</div>
            @endforelse
        </section>

        <section class="card">
            <h3 style="margin:0 0 10px;">Test middleware phan quyen</h3>
            <p style="margin:0;">Truy cap duong dan /admin/phanquyen de test quyen xem_phanquyen.</p>
        </section>
    </div>
</body>
</html>
