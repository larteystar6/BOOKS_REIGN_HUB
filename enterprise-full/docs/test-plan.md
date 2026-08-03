# Phase 2 Test Plan (Draft)

This test plan lists automated and manual tests required to validate Phase 2 changes before merging into main.

Automated tests (unit / integration)
- Auth
  - login success/failure
  - token expiry and refresh behavior
- Products
  - search returns results and respects limit
  - product detail includes images and stock summary
- Images
  - upload accepts allowed formats and rejects invalid files
  - duplicate image (same hash) is ignored
  - thumbnail generation queued and accessible
- Sales
  - create sale success with sufficient stock
  - create sale fails with insufficient stock (409)
  - idempotency: repeat request with same Idempotency-Key returns same result
  - rollback on downstream failure (payment posting failure rolls back sale)
- Sync
  - batch sync processes multiple operations and reports per-item results
  - duplicate operations are deduplicated
- RBAC
  - restricted endpoints return 403 for unauthorized role

End to end (E2E)
- POS flow: add items, pay, confirm DB state (sales + inventory movements + payments + audit logs)
- Offline flow: queue sale offline, reconnect, sync, verify single sale posted

Security tests
- Verify no stack traces exposed in API responses
- Rate limit checks
- Admin helper removal verified (cannot be executed in production branch)

Performance tests
- Product search against 100k SKU dataset (staging)
- Upload 20 images in one batch and validate worker throughput

Manual checks
- Storage link works (php artisan storage:link)
- Worker processes (queue:work) handle thumbnail jobs
- CI job runs unit tests and lints

CI
- Add PHPUnit tests and a test DB setup in CI
- Run frontend unit tests (Jest) and linting (ESLint)

Acceptance criteria
- All automated tests pass in CI
- No critical security findings
- Migrations apply cleanly on staging and roll back cleanly
