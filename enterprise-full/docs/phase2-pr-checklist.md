# Phase 2 PR Checklist

This checklist will be attached to each Phase 2 PR. All items must be satisfied before merge.

- [ ] Migration files added with reversible down() methods
- [ ] No destructive changes without explicit approval
- [ ] API contract updated in docs/api
- [ ] Sync protocol doc updated (docs/sync-protocol.md)
- [ ] Unit and feature tests added and passing
- [ ] RBAC policy placeholders added where changes touch access
- [ ] Audit logging implemented for new write operations
- [ ] CI configured to run tests and static analysis
- [ ] Admin create command removed from enterprise-full main branch (or gated)
- [ ] Runbook for migration and rollback attached
- [ ] Performance considerations documented (indexes, caches)
