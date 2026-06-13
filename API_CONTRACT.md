# Realizalab API — Contract

## Base URL

```
http://localhost/
```

> All routes are at the root level (no `/api` prefix).

---

## Authentication

All routes except `POST /auth/login` require a Bearer token in the `Authorization` header.

```
Authorization: Bearer {token}
```

Tokens expire in **60 minutes**.

---

## Response Format

### Success

```json
{
  "success": true,
  "message": "string",
  "data": {}
}
```

### Error

```json
{
  "success": false,
  "message": "string",
  "errors": {}
}
```

### Paginated list

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "next_cursor": null
  }
}
```

---

## Common Query Parameters (index endpoints)

| Param | Type | Default | Description |
|---|---|---|---|
| `take` | integer | `10` | Items per page. `0` returns all records |
| `order-field` | string | `id` | Field to sort by |
| `order` | string | `desc` | Sort direction: `asc` or `desc` |

---

## HTTP Status Codes

| Code | Description |
|---|---|
| `200` | OK |
| `201` | Created |
| `204` | No Content |
| `401` | Unauthenticated |
| `403` | Unauthorized |
| `404` | Not Found |
| `405` | Method Not Allowed |
| `422` | Validation Error |
| `429` | Too Many Requests |
| `500` | Internal Server Error |

---

## Enums

### OrderTypeEnum
| Value | Description |
|---|---|
| `sus` | SUS (public health system) |
| `particular` | Private |

### FinancialTypeEnum
| Value | Description |
|---|---|
| `in` | Income |
| `out` | Expense |

### FinancialCategoryEnum
| Value |
|---|
| `exam` |
| `food` |
| `transport` |
| `supply` |
| `other` |

### PaymentMethodEnum
| Value |
|---|
| `cash` |
| `pix` |
| `credit_card` |
| `debit_card` |
| `transfer` |
| `other` |

---

## Auth

### Login
```
POST /auth/login
```
**Public route — no token required.**

**Request body**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123..."
  }
}
```

**Response `401`**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {}
}
```

---

### Logout
```
POST /auth/logout
```
Revokes the current token.

**Response `204`**
```json
{
  "success": true,
  "message": "Logout successful",
  "data": null
}
```

---

## User

### Get authenticated user
```
GET /user/me
```

**Response `200`**
```json
{
  "success": true,
  "message": "Authenticated user has been found",
  "data": {
    "id": "01jx...",
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+5511999999999",
    "birthday": "1990-01-15",
    "email_verified_at": null,
    "created_at": "2026-06-07T18:00:00.000000Z",
    "updated_at": "2026-06-07T18:00:00.000000Z",
    "deleted_at": null
  }
}
```

---

### List users
```
GET /user
```
Supports [common query parameters](#common-query-parameters-index-endpoints).

---

### Create user
```
POST /user
```

**Request body**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+5511999999999",
  "birthday": "1990-01-15",
  "password": "password",
  "password_confirmation": "password"
}
```

| Field | Required | Type | Rules |
|---|---|---|---|
| `name` | yes | string | max:255 |
| `email` | yes | string | email, unique |
| `phone` | no | string | max:20 |
| `birthday` | no | string | date |
| `password` | yes | string | min:8, confirmed |

**Response `201`**

---

### Get user
```
GET /user/{id}
```

**Response `200`**

---

### Update user
```
PUT /user/{id}
```

All fields are optional (`sometimes`). `email` ignores uniqueness for the current user.

**Request body**
```json
{
  "name": "New Name",
  "email": "new@example.com",
  "phone": "+5511888888888",
  "birthday": "1990-06-20",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

**Response `200`**

---

### Delete user
```
DELETE /user/{id}
```

**Response `204`**

---

## Partner

### List partners
```
GET /partner
```

### Create partner
```
POST /partner
```

| Field | Required | Type | Rules |
|---|---|---|---|
| `name` | yes | string | max:255 |

### Get partner
```
GET /partner/{id}
```

### Update partner
```
PUT /partner/{id}
```

| Field | Required | Type |
|---|---|---|
| `name` | no | string |

### Delete partner
```
DELETE /partner/{id}
```

---

## Exam

### List exams
```
GET /exam
```
Supports [common query parameters](#common-query-parameters-index-endpoints).

Additional filters:

| Param | Type | Description |
|---|---|---|
| `search_term` | string | Searches in `name`, `code`, `cost`, `price_sus`, `price_particular` |
| `partner_id` | string | Comma-separated partner IDs to filter by |

---

### Create exam
```
POST /exam
```

**Request body**
```json
{
  "name": "Hemograma Completo",
  "code": "HEM001",
  "cost": 10.50,
  "price_sus": 0.00,
  "price_particular": 45.00,
  "partner_id": "01jx..."
}
```

| Field | Required | Type | Rules |
|---|---|---|---|
| `name` | yes | string | max:255 |
| `code` | yes | string | max:255 |
| `cost` | yes | number | min:0 |
| `price_sus` | yes | number | min:0 |
| `price_particular` | yes | number | min:0 |
| `partner_id` | yes | string | ulid, exists |

**Response `201`**

---

### Get exam
```
GET /exam/{id}
```

### Update exam
```
PUT /exam/{id}
```
All fields optional (`sometimes`).

### Delete exam
```
DELETE /exam/{id}
```

---

## Patient

### List patients
```
GET /patient
```

Additional filters:

| Param | Type | Description |
|---|---|---|
| `search_term` | string | Searches in `name`, `document`, `email`, `phone`, `observations` |

---

### Create patient
```
POST /patient
```

**Request body**
```json
{
  "name": "Maria Silva",
  "document": "123.456.789-00",
  "email": "maria@example.com",
  "phone": "+5511999999999",
  "birthday": "1985-03-22",
  "observations": "Alérgica a dipirona"
}
```

| Field | Required | Type | Rules |
|---|---|---|---|
| `name` | yes | string | max:255 |
| `document` | no | string | max:255 |
| `email` | no | string | email, max:255 |
| `phone` | no | string | max:20 |
| `birthday` | no | string | date |
| `observations` | no | string | — |

**Response `201`**

---

### Get patient
```
GET /patient/{id}
```
Response includes the patient's `orders`.

### Update patient
```
PUT /patient/{id}
```
All fields optional (`sometimes`).

### Delete patient
```
DELETE /patient/{id}
```

---

## Order

### List orders
```
GET /order
```
Response includes `patient` and `exams` relations.

Additional filters:

| Param | Type | Description |
|---|---|---|
| `search_term` | string | Filters by patient name |
| `patient_id` | string | Comma-separated patient IDs to filter by |

---

### Create order
```
POST /order
```
Creates the order and automatically generates `order_exams` records. Exam prices are selected based on the order type (`price_sus` or `price_particular`).

**Request body**
```json
{
  "type": "particular",
  "patient_id": "01jx...",
  "exams": ["01jx...", "01jx..."]
}
```

| Field | Required | Type | Rules |
|---|---|---|---|
| `type` | yes | string | `sus` or `particular` |
| `patient_id` | no | string | ulid, exists |
| `exams` | no | array | — |
| `exams.*` | yes (if exams sent) | string | exists in exams |

**Response `201`**

---

### Get order
```
GET /order/{id}
```
Response includes `patient`, `exams`, and `orderExams`.

---

### Update order
```
PUT /order/{id}
```
If `exams` is sent, existing `order_exams` are **deleted and recreated** with updated prices. The entire operation runs inside a database transaction with a row-level lock.

**Request body**
```json
{
  "type": "sus",
  "patient_id": "01jx...",
  "exams": ["01jx..."]
}
```

All fields optional (`sometimes`).

**Response `200`**

---

### Delete order
```
DELETE /order/{id}
```

---

## Order Exam

### List order exams
```
GET /order-exam
```

### Create order exam
```
POST /order-exam
```

**Request body**
```json
{
  "order_id": "01jx...",
  "exam_id": "01jx...",
  "exam_name": "Hemograma Completo",
  "exam_price": 45.00
}
```

| Field | Required | Type | Rules |
|---|---|---|---|
| `order_id` | yes | string | ulid, exists |
| `exam_id` | yes | string | ulid, exists |
| `exam_name` | yes | string | max:255 |
| `exam_price` | yes | number | min:0 |

**Response `201`**

---

### Get order exam
```
GET /order-exam/{id}
```

### Update order exam
```
PUT /order-exam/{id}
```
All fields optional (`sometimes`).

### Delete order exam
```
DELETE /order-exam/{id}
```

---

## Financial

### List financial records
```
GET /financial
```

### Create financial record
```
POST /financial
```

**Request body**
```json
{
  "amount": 150.00,
  "paid_at": "2026-06-07",
  "type": "in",
  "category": "exam",
  "payment_method": "pix",
  "description": "Pagamento pedido #01jx...",
  "financialable_id": "01jx...",
  "financialable_type": "App\\Models\\Order"
}
```

| Field | Required | Type | Rules |
|---|---|---|---|
| `amount` | yes | number | min:0 |
| `paid_at` | yes | string | date |
| `type` | yes | string | `in` or `out` |
| `category` | yes | string | see [FinancialCategoryEnum](#financialcategoryenum) |
| `payment_method` | yes | string | see [PaymentMethodEnum](#paymentmethodenum) |
| `description` | no | string | — |
| `financialable_id` | no | string | — |
| `financialable_type` | no | string | — |

**Response `201`**

---

### Get financial record
```
GET /financial/{id}
```

### Update financial record
```
PUT /financial/{id}
```
All fields optional (`sometimes`).

### Delete financial record
```
DELETE /financial/{id}
```
