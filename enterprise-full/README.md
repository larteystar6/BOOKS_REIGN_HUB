# enterprise-full — POS Enterprise (scaffold)

This branch contains the initial scaffold and planning artifacts for the Enterprise POS project.

What's included in this commit:
- Composer metadata (minimal) for Laravel installation.
- GitHub Actions CI skeleton for PHP unit tests and static checks.
- Initial SQL migration (schema) for core tables: users, roles, products, product_images, sales, sale_items, sync_queue, audit_logs.
- Architecture & roadmap documents describing phases and milestones.

Next steps (automated):
1. I will push a working Laravel application skeleton (composer create-project) and wire up the migrations.
2. Implement authentication, RBAC, product image APIs, and frontend PWA components in the enterprise-full branch.

If you want me to run composer install and push the full Laravel vendor directories, say so (but note vendors are usually not committed).
