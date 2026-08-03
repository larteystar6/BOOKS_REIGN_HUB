# Phase 1 plan & checklist

This PR implements Phase 1 core features for review and local testing.

Included in this phase:
- Authentication endpoints (login/logout/me)
- Sales creation API (transactional)
- Product image upload API (already added in Phase 0)
- Frontend Vue placeholders: ProductEditor and POS components, IndexedDB sync stub

Local acceptance tests:
1. Run migrations and create admin (php artisan app:create-admin ...)
2. Create a test product and upload images via Product Editor (frontend)
3. Create a sale via POS component and verify DB records

Next steps after merge:
- Implement RBAC middleware & policies
- Improve product search API and integrate with POS front-end
- Add offline queue visuals and background sync scheduling
