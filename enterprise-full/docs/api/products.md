# API Contracts - Products

## GET /api/products/search

Search by SKU, name, barcode.

Query params
- query (string) — required
- limit (int) — optional (default 20, max 200)
- warehouse_uuid (uuid) — optional: include per-warehouse availability

Response 200
{
  "data": [
    { "uuid":"...", "sku":"SKU123", "name":"Product name", "price":"100.00", "barcode":"...", "stock": {"warehouse_uuid":"...","on_hand":10} }
  ],
  "meta": { "limit":20 }
}

## GET /api/products/{uuid}

Response 200
{
  "uuid":"...",
  "sku":"...",
  "name":"...",
  "description":"...",
  "price":"...",
  "cost":"...",
  "images":[ {"uuid":"...","thumb_path":"...","storage_path":"...","is_primary":true} ],
  "stock_summary": [ { "warehouse_uuid":"...","on_hand":10, "reserved":2 } ]
}

Notes
- Implement server-side pagination and limit result sizes.
- Recommend full-text indexing for product name/description once migrated to large catalogs or add an external search engine (Meili/Elastic) in a later phase.
