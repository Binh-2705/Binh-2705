# Laravel Service Gateway

Laravel app nay dong vai tro web app chinh va API gateway cho he thong nhan su tach theo service/database.

## Kien truc

- Root app: Laravel trong `laravel_app`
- Generic gateway: `/api/services/{service}/{resource}`
- Service alias gateway: `/api/hr/...`, `/api/payroll/...`, `/api/attendance/...`
- Web console: `/services`

## Service da map

- `hr`: nhan su, phong ban, tai khoan, ho so, chuc vu, phan cong, bao hiem, nghi phep, khen thuong/ky luat, vai tro, quyen, session, reset token
- `payroll`: bang luong, bac luong, ngach luong, hop dong, lich su luong
- `attendance`: cham cong, cau hinh cham cong, tong hop cong theo view
- `recruitment`: ung vien, dot tuyen dung, ho so ung tuyen, lich phong van, danh gia phong van
- `training`: khoa dao tao, tham gia dao tao
- `reporting`: bao cao
- `chatbot`: sessions, action drafts, messages

## Xac thuc API

Dat `SERVICE_GATEWAY_TOKEN` trong `.env` va gui mot trong hai header sau:

- `X-Service-Token: your-token`
- `Authorization: Bearer your-token`

## Endpoint chinh

- `GET /api/services`: catalog tong
- `GET /api/{service}`: catalog rieng tung service
- `GET /api/{service}/{resource}`: danh sach ban ghi
- `GET /api/{service}/{resource}/{id}`: chi tiet ban ghi
- `POST /api/{service}/{resource}`: tao ban ghi
- `PUT /api/{service}/{resource}/{id}`: cap nhat ban ghi
- `DELETE /api/{service}/{resource}/{id}`: xoa ban ghi

Chi tiet frontend xem tai [docs/service-alias-api.md](docs/service-alias-api.md).

## Dinh danh ban ghi

- Single key: dung truc tiep gia tri khoa chinh, vi du `GET /api/hr/employees/15`
- Composite key: ghep bang dau phay theo dung thu tu khoa trong registry, vi du `GET /api/hr/account-roles/7,2`
- Read-only resource: hien tai `attendance-summaries` chi ho tro `GET`

## Web console

- `GET /services`: xem tat ca service/resource da map
- `GET /services/{service}/{resource}`: xem du lieu co phan trang
- `GET /services/{service}/{resource}/create`: tao ban ghi cho resource cho phep ghi
- `GET /services/{service}/{resource}/{id}/edit`: sua ban ghi, co ho tro composite key

## Kiem tra

Chay:

```bash
php artisan route:list
php artisan test
```
