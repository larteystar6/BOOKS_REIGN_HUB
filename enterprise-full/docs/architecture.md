# Architecture Overview

This document describes the high-level architecture for the POS Enterprise system.

- Backend: Laravel 10 (PHP 8.1+), REST APIs for business operations, queued jobs for background processing.
- Frontend: Vue 3 + Vite as a PWA for offline-first behavior. IndexedDB used for local queues.
- Database: MariaDB with UUID primary keys, audit/history tables, sync metadata.
- CI: GitHub Actions to run tests and static checks.

The repository branch enterprise-full will contain iterative feature branches implementing the modules listed in the roadmap.
