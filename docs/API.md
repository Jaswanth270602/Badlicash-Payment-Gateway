# BadliCash API Documentation

## Base URL

```
Test: http://localhost:8000/api/v1
Production: https://your-domain.com/api/v1
```

## Authentication

All API requests require an API key. Include it in the request header:

```
X-API-Key: your-api-key-here
```

Or as a Bearer token:

```
Authorization: Bearer your-api-key-here
```

## Rate Limiting

- **60 requests per minute** (default)
- **1000 requests per hour** (default)

Rate limits can be configured per API key.

---

## Endpoints

### 1. Create Payment

Create a new payment and process it immediately.

**Endpoint:** `POST /api/v1/payment`

**Headers:**
```
X-API-Key: your-api-key
Content-Type: application/json
```

**Request Body:**
```json
{
  "amount": 100.00,
  "currency": "USD",
  "payment_method": "card",
  "customer_details": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890"
  },
  "description": "Payment for Order #12345",
  "metadata": {
    "order_id": "ORD123",
    "customer_id": "CUST456"
  },
  "return_url": "https://yoursite.com/payment/success",
  "cancel_url": "https://yoursite.com/payment/cancel",
  "idempotency_key": "unique-key-12345"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "data": {
    "order_id": "ORD_ABC123XYZ456",
    "transaction_id": "TXN_DEF789GHI012",
    "amount": 100.00,
    "currency": "USD",
    "status": "success",
    "payment_method": "card",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Error Response:** `422 Unprocessable Entity`
```json
{
  "error": "Validation failed",
  "messages": {
    "amount": ["The amount field is required."]
  }
}
```

---

### 2. Verify Payment

Verify the status of a payment transaction.

**Endpoint:** `GET /api/v1/payment/{transactionId}/verify`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "transaction_id": "TXN_DEF789GHI012",
    "status": "success",
    "verified": true,
    "amount": 100.00,
    "currency": "USD"
  }
}
```

---

### 3. Get Order Details

Retrieve details of a specific order.

**Endpoint:** `GET /api/v1/orders/{orderId}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "order_id": "ORD_ABC123XYZ456",
    "amount": 100.00,
    "currency": "USD",
    "status": "completed",
    "customer_details": {
      "name": "John Doe",
      "email": "john@example.com"
    },
    "description": "Payment for Order #12345",
    "test_mode": true,
    "transactions": [
      {
        "transaction_id": "TXN_DEF789GHI012",
        "payment_method": "card",
        "amount": 100.00,
        "status": "success",
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "created_at": "2024-01-15T10:28:00Z",
    "updated_at": "2024-01-15T10:30:15Z"
  }
}
```

---

### 4. List Orders

Get a paginated list of orders for the authenticated merchant.

**Endpoint:** `GET /api/v1/orders`

**Query Parameters:**
- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 10, max: 100)
- `status` (string): Filter by status (created, pending, completed, failed, cancelled, expired)
- `test_mode` (boolean): Filter by test mode

**Example:** `GET /api/v1/orders?page=1&per_page=25&status=completed`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "order_id": "ORD_ABC123",
      "amount": 100.00,
      "currency": "USD",
      "status": "completed",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 25,
    "total": 150,
    "last_page": 6
  }
}
```

---

### 5. List Transactions

Get a paginated list of transactions.

**Endpoint:** `GET /api/v1/transactions`

**Query Parameters:**
- `page` (int): Page number
- `per_page` (int): Items per page
- `status` (string): Filter by status
- `payment_method` (string): Filter by payment method
- `from_date` (date): Filter from date (YYYY-MM-DD)
- `to_date` (date): Filter to date (YYYY-MM-DD)

**Response:** `200 OK` (similar to orders)

---

### 6. Get Transaction Details

Retrieve details of a specific transaction including refunds.

**Endpoint:** `GET /api/v1/transactions/{transactionId}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "transaction_id": "TXN_DEF789GHI012",
    "order_id": "ORD_ABC123XYZ456",
    "amount": 100.00,
    "fee_amount": 2.80,
    "net_amount": 97.20,
    "currency": "USD",
    "payment_method": "card",
    "status": "success",
    "refunds": [
      {
        "refund_id": "RFD_XYZ123ABC456",
        "amount": 50.00,
        "status": "completed",
        "created_at": "2024-01-16T14:20:00Z"
      }
    ],
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### 7. Create Refund

Initiate a full or partial refund for a successful transaction.

**Endpoint:** `POST /api/v1/refunds`

**Request Body:**
```json
{
  "transaction_id": "TXN_DEF789GHI012",
  "amount": 50.00,
  "reason": "Customer requested refund"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "data": {
    "refund_id": "RFD_XYZ123ABC456",
    "transaction_id": "TXN_DEF789GHI012",
    "amount": 50.00,
    "currency": "USD",
    "status": "completed",
    "is_partial": true,
    "created_at": "2024-01-16T14:20:00Z"
  }
}
```

**Error:** `400 Bad Request`
```json
{
  "error": "Cannot refund unsuccessful transaction"
}
```

---

### 8. List Refunds

Get all refunds for the merchant.

**Endpoint:** `GET /api/v1/refunds`

**Query Parameters:**
- `page`, `per_page`
- `status` (string): Filter by status

---

### 9. Create Payment Link

Generate a payment link for customers.

**Endpoint:** `POST /api/v1/payment_links`

**Request Body:**
```json
{
  "title": "Invoice #12345 Payment",
  "description": "Payment for services rendered",
  "amount": 250.00,
  "currency": "USD",
  "customer_details": {
    "name": "Jane Smith",
    "email": "jane@example.com"
  },
  "max_usage": 1,
  "expires_in": 86400,
  "success_url": "https://yoursite.com/payment/success",
  "cancel_url": "https://yoursite.com/payment/cancel",
  "metadata": {
    "invoice_id": "INV-12345"
  }
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "data": {
    "link_token": "abc123xyz456def789ghi012",
    "payment_url": "http://localhost:8000/pay/abc123xyz456def789ghi012",
    "title": "Invoice #12345 Payment",
    "amount": 250.00,
    "currency": "USD",
    "status": "active",
    "expires_at": "2024-01-16T10:30:00Z",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### 10. List Payment Links

Get all payment links for the merchant.

**Endpoint:** `GET /api/v1/payment_links`

**Query Parameters:**
- `page`, `per_page`
- `status` (string): Filter by status (active, expired, paid, cancelled)

---

### 11. Test Webhook

Test merchant webhook endpoint.

**Endpoint:** `POST /api/v1/webhooks/test`

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Test webhook delivered successfully",
  "response_status": 200
}
```

---

## Webhook Events

BadliCash sends webhooks to your configured URL for the following events:

### Event: `payment.created`

```json
{
  "event_type": "payment.created",
  "order_id": "ORD_ABC123",
  "amount": 100.00,
  "currency": "USD",
  "status": "created",
  "created_at": "2024-01-15T10:30:00Z"
}
```

### Event: `payment.success`

```json
{
  "event_type": "payment.success",
  "order_id": "ORD_ABC123",
  "transaction_id": "TXN_DEF789",
  "amount": 100.00,
  "currency": "USD",
  "payment_method": "card",
  "status": "success",
  "captured_at": "2024-01-15T10:30:15Z"
}
```

### Event: `payment.failed`

```json
{
  "event_type": "payment.failed",
  "order_id": "ORD_ABC123",
  "transaction_id": "TXN_DEF789",
  "amount": 100.00,
  "currency": "USD",
  "status": "failed",
  "error": "Insufficient funds"
}
```

### Event: `refund.created`

```json
{
  "event_type": "refund.created",
  "refund_id": "RFD_XYZ123",
  "transaction_id": "TXN_DEF789",
  "amount": 50.00,
  "currency": "USD",
  "status": "completed",
  "is_partial": true,
  "processed_at": "2024-01-16T14:20:00Z"
}
```

## Webhook Signature Verification

All webhooks include an `X-BadliCash-Signature` header. Verify it using HMAC SHA256:

```php
$signature = hash_hmac('sha256', json_encode($payload), $webhookSecret);
$isValid = hash_equals($signature, $requestSignature);
```

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized (Invalid API Key) |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests (Rate Limited) |
| 500 | Internal Server Error |

## Idempotency

Include an `Idempotency-Key` header or field to ensure safe retries:

```json
{
  "idempotency_key": "unique-request-id-12345",
  "amount": 100.00
}
```

Requests with the same idempotency key will return the original result without creating duplicate resources.

## SDKs and Libraries

Currently, BadliCash provides a REST API. Language-specific SDKs can be generated from the OpenAPI specification.

## Support

For API questions, contact: support@badlicash.com

