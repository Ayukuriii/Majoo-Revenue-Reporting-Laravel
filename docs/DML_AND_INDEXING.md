# DML and report indexing

The application writes through Eloquent / the query builder. The SQL below is for reviewers: every statement uses bound parameters (`?`). Do not concatenate user input into SQL.

`bill_total` is `decimal(15,2)`, not the assignment’s `double`. See [decimal vs double](#decimal-vs-double).

## DML examples

### Merchants

```sql
INSERT INTO merchants (user_id, merchant_name, created_by, updated_by, created_at, updated_at)
VALUES (?, ?, ?, ?, ?, ?);

UPDATE merchants
SET merchant_name = ?, updated_by = ?, updated_at = ?
WHERE id = ?;

DELETE FROM merchants WHERE id = ?;
```

`user_id` is unique (one merchant per login user). An insert that reuses an existing `user_id` fails with a unique-constraint error.

### Outlets

```sql
INSERT INTO outlets (merchant_id, outlet_name, created_by, updated_by, created_at, updated_at)
VALUES (?, ?, ?, ?, ?, ?);

UPDATE outlets
SET outlet_name = ?, updated_by = ?, updated_at = ?
WHERE id = ? AND merchant_id = ?;

DELETE FROM outlets WHERE id = ? AND merchant_id = ?;
```

Scope updates and deletes by `merchant_id` as well as `id` so a tenant cannot mutate another merchant’s row.

### Transactions

```sql
INSERT INTO transactions (merchant_id, outlet_id, bill_total, created_by, updated_by, created_at, updated_at)
VALUES (?, ?, ?, ?, ?, ?, ?);

UPDATE transactions
SET bill_total = ?, updated_by = ?, updated_at = ?
WHERE id = ? AND merchant_id = ?;

DELETE FROM transactions WHERE id = ? AND merchant_id = ?;
```

Reports filter by `merchant_id` (and optionally `outlet_id`) plus a `created_at` range. They never take `merchant_id` from the client; tenancy comes from the JWT user.

## Indexes

| Table | Index name | Columns | Why it exists |
| --- | --- | --- | --- |
| `merchants` | `merchants_user_id_unique` | `user_id` | 1:1 login: `Tenant` / JWT claims resolve `merchants.user_id = users.id`. Unique enforces one merchant per user and replaces the old non-unique `merchants_user_id_index`. |
| `outlets` | `outlets_merchant_id_index` | `merchant_id` | `GET /api/outlets` and outlet-ownership checks (`WHERE merchant_id = ?`). Unchanged from the create-table migration. |
| `transactions` | `transactions_merchant_id_created_at_index` | `merchant_id`, `created_at` | Merchant monthly report: equality on `merchant_id` plus a month range on `created_at`. Leftmost `merchant_id` also covers FK lookups, so the standalone `transactions_merchant_id_index` was dropped. |
| `transactions` | `transactions_merchant_id_outlet_id_created_at_index` | `merchant_id`, `outlet_id`, `created_at` | Outlet monthly report: `merchant_id` + `outlet_id` + date range. |
| `transactions` | `transactions_outlet_id_index` | `outlet_id` | FK on `outlet_id`. Kept because the three-column index does not start with `outlet_id`. |

### Composite vs two indexes

The merchant report is `WHERE merchant_id = ? AND created_at BETWEEN ? AND ?`.

The three-column index `(merchant_id, outlet_id, created_at)` cannot use `created_at` as a range for that query: InnoDB’s leftmost-prefix rule requires `outlet_id` to be an equality (or skipped only if it is the last unused column). With `outlet_id` in the middle, a month scan would not ride `created_at`. That is why `(merchant_id, created_at)` exists in addition to the three-column index.

`(outlet_id, created_at)` was **not** added. `ReportService` always applies `forMerchant()` first, so the engine never filters by outlet and date without `merchant_id`.

## Why `created_at` is in the index

Reports load one calendar month:

```sql
WHERE merchant_id = ?
  AND created_at >= ?
  AND created_at <= ?
GROUP BY DATE(created_at)
```

Putting `created_at` after the tenant key lets InnoDB range-scan that month and aggregate `SUM(bill_total)` without reading every row for the merchant. `DATE(created_at)` in `SELECT` / `GROUP BY` does not stop the optimizer from using the range on the underlying `created_at` column.

## Why `bill_total` is not indexed

`bill_total` is never in `WHERE`, `JOIN`, or `ORDER BY`. It is only aggregated (`SUM`) after the merchant/outlet/date filter. An index on amount would not shrink the report scan and would add write cost on every insert/update.

## EXPLAIN (optional)

Merchant report (uses `transactions_merchant_id_created_at_index` when the optimizer agrees):

```sql
EXPLAIN
SELECT DATE(created_at) AS report_date, SUM(bill_total) AS omzet
FROM transactions
WHERE merchant_id = ?
  AND created_at >= ?
  AND created_at <= ?
GROUP BY DATE(created_at);
```

Outlet report (uses `transactions_merchant_id_outlet_id_created_at_index`):

```sql
EXPLAIN
SELECT DATE(created_at) AS report_date, SUM(bill_total) AS omzet
FROM transactions
WHERE merchant_id = ?
  AND outlet_id = ?
  AND created_at >= ?
  AND created_at <= ?
GROUP BY DATE(created_at);
```

Look for `ref` / `range` on those composites, not a full table scan. Bind the same values the API uses (tenant `merchant_id`, month start/end in `app.timezone`).

## Decimal vs double

The assignment sketch used `double` for `bill_total`. The schema uses `decimal(15,2)` instead so money is stored with exact cents and is not subject to binary floating-point rounding. That choice is documented on the `transactions` create migration (`database/migrations/2026_08_13_000003_create_transactions_table.php`). The Eloquent cast is `decimal:2`. Report omzet is still `SUM(bill_total)`; only the storage type changed.
