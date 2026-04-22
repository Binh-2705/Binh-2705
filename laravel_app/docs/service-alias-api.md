# Service Alias API

Tai lieu nay danh cho frontend goi truc tiep cac endpoint alias theo service.

## 1. Auth

Gui token chung qua header:

```http
X-Service-Token: your-token
```

Hoac:

```http
Authorization: Bearer your-token
```

## 2. Pattern endpoint

```text
GET    /api/{service}
GET    /api/{service}/{resource}
GET    /api/{service}/{resource}/{id}
POST   /api/{service}/{resource}
PUT    /api/{service}/{resource}/{id}
DELETE /api/{service}/{resource}/{id}
```

Body `POST` va `PUT` la JSON object.

## 3. Format response

Danh sach:

```json
{
  "ok": true,
  "service": "hr",
  "resource": "employees",
  "connection": "hr_service",
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 50
  },
  "data": [
    {
      "MaNV": 1,
      "HoTen": "Nguyen Van A",
      "__resource_id": "1"
    }
  ]
}
```

Chi tiet:

```json
{
  "ok": true,
  "service": "hr",
  "resource": "employees",
  "connection": "hr_service",
  "data": {
    "MaNV": 1,
    "HoTen": "Nguyen Van A",
    "__resource_id": "1"
  }
}
```

Loi:

```json
{
  "ok": false,
  "message": "Resource is read-only."
}
```

## 4. Record ID

- Single key: `15`
- Composite key: `7,2`
- Attendance summary view: `15,4,2026`

Frontend nen dung truong `__resource_id` tra ve tu API thay vi tu ghep ID thu cong.

## 5. Service catalog

### HR

- Base: `/api/hr`
- Resources:
  - `employees`
  - `departments`
  - `accounts`
  - `employee-profiles`
  - `positions`
  - `assignments`
  - `insurances`
  - `leave-requests`
  - `reward-discipline-types`
  - `reward-discipline-records`
  - `roles`
  - `features`
  - `role-permissions`
  - `account-roles`
  - `notification-flags`
  - `session-audits`
  - `password-reset-tokens`

### Payroll

- Base: `/api/payroll`
- Resources:
  - `payrolls`
  - `salary-grades`
  - `salary-bands`
  - `contracts`
  - `salary-history`
  - `contract-salary-history`

### Attendance

- Base: `/api/attendance`
- Resources:
  - `attendance-records`
  - `attendance-configs`
  - `attendance-summaries` read-only

### Recruitment

- Base: `/api/recruitment`
- Resources:
  - `candidates`
  - `recruitment-campaigns`
  - `applications`
  - `interviews`
  - `interview-reviews`

### Training

- Base: `/api/training`
- Resources:
  - `courses`
  - `participants`

### Reporting

- Base: `/api/reporting`
- Resources:
  - `reports`

### Chatbot

- Base: `/api/chatbot`
- Resources:
  - `sessions`
  - `action-drafts`
  - `messages`

## 6. Frontend example

```js
const token = 'your-token';

async function fetchEmployees(page = 1) {
  const response = await fetch(`/api/hr/employees?page=${page}&limit=20`, {
    headers: {
      'Accept': 'application/json',
      'X-Service-Token': token,
    },
  });

  if (!response.ok) {
    throw new Error(`API error: ${response.status}`);
  }

  return response.json();
}

async function updateAccountRole(resourceId, payload) {
  const response = await fetch(`/api/hr/account-roles/${encodeURIComponent(resourceId)}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Service-Token': token,
    },
    body: JSON.stringify(payload),
  });

  return response.json();
}
```

## 7. Frontend rule nhanh

- Luon goi `GET /api/{service}` de lay catalog neu can build UI dong.
- Luon luu `__resource_id` trong state cua bang/list.
- Khong hien nut tao/sua/xoa cho resource read-only.
- Neu API tra `401`, kiem tra token.
- Neu API tra `404`, kiem tra service/resource/id.
- Neu API tra `405`, resource dang bi khoa ghi.