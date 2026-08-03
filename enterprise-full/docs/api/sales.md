# API Contracts - Sales

## POST /api/sales

Headers
- Authorization: Bearer <jwt>
- Idempotency-Key: <uuid>  // required for write idempotency

Request body
{
  "invoice_no": "INV-2026-0001",
  "cashier_uuid": "<uuid>",
  "customer_uuid": "<uuid or null>",
  "warehouse_uuid": "<uuid>",
  "items": [
    { "product_uuid":"<uuid>", "quantity":1.00, "unit_price":100.00, "discount":0.00, "tax":0.00 }
  ],
  "payments": [
    { "method":"cash", "amount":100.00 }
  ],
  "notes": "optional"
}

Successful response 201
{
  "success": true,
  "uuid": "<sale-uuid>",
  "invoice_no": "INV-2026-0001",
  "total": 100.00,
  "posted_at": "2026-08-03T12:00:00Z"
}

Error responses
- 409 Conflict — insufficient stock
  { "error":"insufficient_stock", "details": [ { "product_uuid":"...", "available":3, "required":5 } ] }
- 422 Validation errors
- 401/403 for auth/authorization
- 500 server error (messages sanitized)

Behavior & guarantees
- The endpoint is idempotent when called with the same Idempotency-Key: the server will detect duplicates and return the same response without creating duplicates.
- Inventory adjustments occur within the same DB transaction as sale creation. If stock update fails, the whole transaction rolls back.
- Audit log entry is written for every sale creation.

Validation
- Quantity must be > 0
- Unit price must be >= 0
- invoice_no must be unique; server generates a unique internal reference if missing

Notes
- Payments and accounting posting are done atomically where possible; partial failures roll back the sale and return a descriptive error.
