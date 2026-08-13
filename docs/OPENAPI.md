# OpenAPI contract

The API contract is defined with OpenAPI 3 PHP 8 attributes under `app/OpenApi/`. L5-Swagger scans that directory and writes JSON/YAML for Swagger UI.

## Generate

```bash
php artisan l5-swagger:generate
# or
composer docs:openapi
```

With `L5_SWAGGER_GENERATE_ALWAYS=true` (see `.env.example`), the spec is rebuilt when you open the UI. On production set that to `false` and generate during deploy if you still expose Swagger.

## URLs (same origin)

Local: `http://majoo-revenue-reporting.test`. Production: `https://APP_DOMAIN`.

| What | Path |
| --- | --- |
| Swagger UI | `/api/documentation` |
| OpenAPI JSON | `/docs` |
| Generated files | `storage/api-docs/api-docs.json`, `storage/api-docs/api-docs.yaml` |

## Authorize with JWT

1. `POST /api/auth/login` with a seed user:

   | Email                   | Password   |
   | ----------------------- | ---------- |
   | `merchant1@example.com` | `password` |
   | `merchant2@example.com` | `password` |

   Example body: `{ "email": "merchant1@example.com", "password": "password" }`.
2. Copy `data.token` from the JSON body.
3. In Swagger UI click **Authorize**, scheme **bearerAuth**, paste the token only (no `Bearer ` prefix; the UI adds it).
4. Call protected paths (`/api/auth/me`, `/api/outlets`, reports). Logout or a missing token returns **401**.

## Implemented endpoints

| Method | Path | Auth | Notes |
| --- | --- | --- | --- |
| `GET` | `/api/health` | no | `{ "data": { "status": "ok" } }` |
| `POST` | `/api/auth/login` | no | Throttled. Returns `token`, `token_type` (`bearer`), `expires_in` |
| `POST` | `/api/auth/logout` | JWT | |
| `POST` | `/api/auth/refresh` | JWT | New token |
| `GET` | `/api/auth/me` | JWT | Current user + merchant |
| `GET` | `/api/outlets` | JWT | Outlets for the token merchant only |
| `GET` | `/api/reports/merchant` | JWT | Query: `year`, `month`, `page`, `per_page`. Tenant from JWT |
| `GET` | `/api/reports/outlet` | JWT | Query: `outlet_id`, `year`, `month`, `page`, `per_page`. Foreign outlet → **403** |

`merchant_id` in the query string is ignored for filtering.

## Contract vs implemented routes

When you add a real endpoint, update the matching class in `app/OpenApi/Paths/` and regenerate. Do not put OpenAPI attributes on controllers.
