# Phase 2 Migration Plan (Draft)

This document outlines the stepwise migration approach to add enterprise schema elements and new tables with zero-downtime and rollback guidance.

Phases
- M1: Add enterprise metadata columns to existing tables (nullable defaults)
- M2: Add new core tables (stock_items, inventory_movements, payments, journal tables, sync_queue, audit_logs)
- M3: Backfill data (inventory on_hand, customer balances) using idempotent scripts
- M4: Deploy application code behind feature flags that uses the new columns & tables
- M5: Enable idempotency checks and server-side sync processing
- M6: Cleanup and optional removal of legacy columns after retention period

Pre-flight
1. Full DB logical dump and file storage backup
2. Create a staging copy of production for dry-run
3. Review migration runtime impact (index creation may lock tables) — use `pt-online-schema-change` or online schema change techniques if necessary

Rollback
- Use migration `down()` where available for additive changes
- For destructive changes, prefer creating new columns/tables and switching pointers in app logic, then drop old schema after retention

Monitoring
- Monitor DB locks, replication lag (if any), API error rates, and key business metrics (sales created per minute) during rollout

Deliverables
- SQL migration files (non-destructive) drafted
- Backfill scripts (idempotent) drafted
- Runbook for deploy & rollback
