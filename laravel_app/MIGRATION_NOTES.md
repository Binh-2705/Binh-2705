# Laravel migration notes (phase 1)

## Da thuc hien

- Tao skeleton Laravel 9 trong thu muc `laravel_app`.
- Cap nhat ket noi DB trong `.env` sang `quanlynhansu`.
- Tao luong dang nhap session tuong thich he thong cu (`MaTK`, `taikhoan`, `quyen`).
- Tao middleware:
  - `session.auth`: bat buoc dang nhap.
  - `permission:{ten_quyen}`: check quyen theo bang phan quyen.
- Tao route:
  - `GET /login`
  - `POST /login`
  - `POST /logout`
  - `GET /dashboard`
  - `GET /admin/phanquyen` (test quyen `xem_phanquyen`)

## File moi/chinh sua

- `.env`
- `routes/web.php`
- `app/Http/Kernel.php`
- `app/Services/PermissionService.php`
- `app/Http/Middleware/SessionAuthMiddleware.php`
- `app/Http/Middleware/PermissionMiddleware.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/DashboardController.php`
- `resources/views/auth/login.blade.php`
- `resources/views/dashboard/index.blade.php`

## Luu y

Da xu ly xong dependency va co `vendor/autoload.php`.

Da sua loi 500 do thieu `APP_KEY` bang cach cap nhat `.env`.

Da test runtime:

- `GET /login` tra ve `200`.
- `GET /dashboard` khi chua dang nhap tra ve `302` ve `/login` (dung middleware).
- `GET /admin/phanquyen` khi chua dang nhap tra ve `302` ve `/login`.

Lenh chay nhanh:

```powershell
cd laravel_app
php artisan serve
```
