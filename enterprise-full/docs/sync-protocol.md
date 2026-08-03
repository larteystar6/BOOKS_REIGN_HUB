# Sync Protocol (Client ⇄ Server)

This document describes the offline synchronization protocol used by the PWA client (IndexedDB queue) and the server sync endpoint.

Goals
- Idempotent operations
- Batched synchronization
- Per-operation status reporting
- Conflict detection and deterministic resolution options

Client-side rules
1. Every operation queued locally MUST include an `idempotency_key` (UUID v4), `type` (sale|image|payment...), `payload`, `client_timestamp`, and optional `meta` (device id, app version).
2. Operations are stored in IndexedDB with status: pending, processing, failed, conflict, done.
3. On connectivity, client sends batches (size configurable, e.g., 20 operations) to POST /api/sync/batch.
4. Client includes an auth token and a Client-Id header. If operation already has server_id, client must not resend it.
5. On repeated transient failures, exponential backoff is used; after N attempts (configurable, default 5) mark operation as `failed` and surface to user.

Server-side rules (POST /api/sync/batch)
1. Endpoint accepts a JSON body:
   {
     "operations": [ { "id":"client-op-id","type":"sale","idempotency_key":"...","payload":{...},"client_timestamp":"..." }, ... ]
   }
2. For each operation:
   a. Validate idempotency_key uniqueness using `sync_queue` unique index.
   b. If idempotency_key already present with status `done`, return stored result (no reprocessing).
   c. If not present, insert into `sync_queue` with status `processing` and process operation:
       - For `sale`: validate stock; if insufficient, return conflict with available numbers.
       - For `image_upload`: store file content (or a pointer if uploaded prior), schedule image processing job, return pending status.
   d. On success, mark sync_queue.status = done and store server_id (e.g., sale uuid).
   e. On fatal error, mark `failed` with error details; on transient error, leave as `pending` for retry.
3. Response structure per operation:
   {
     "id":"client-op-id",
     "status":"ok|failed|conflict|pending",
     "server_id":"<uuid|null>",
     "message":"...",
     "conflict":{"server_state":{...},"client_state":{...}}
   }

Conflict handling
- Inventory shortage: respond with status `conflict` and include `details` with current availability.
- Version mismatch: include server entity version and allow client to request a merge UI.
- Duplicates: respond with existing server resource identifier and status `ok`.

Idempotency enforcement
- Server stores `idempotency_key` in `sync_queue` with unique index.
- For write endpoints outside /api/sync/batch, accept Idempotency-Key header and consult `sync_queue` before processing.

Security & validation
- All requests require Authorization header (Bearer <jwt>).
- Validate operation payload schema per type before processing.
- Rate-limit sync endpoint to avoid abuse.

Example client batch request
{
  "operations": [
    {
      "id":"op-1",
      "type":"sale",
      "idempotency_key":"11111111-2222-3333-4444-555555555555",
      "payload": { ... },
      "client_timestamp":"2026-08-03T12:00:00Z"
    }
  ]
}

Example server response
{
  "results": [ { "id":"op-1","status":"ok","server_id":"sale-uuid" } ],
  "summary": { "processed":1, "failed":0, "conflicts":0 }
}
