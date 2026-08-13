# OpenAPI contract

The API contract is defined with OpenAPI 3 PHP 8 attributes under `app/OpenApi/`. L5-Swagger scans that directory and writes JSON/YAML for Swagger UI.

## Generate

```bash
php artisan l5-swagger:generate
# or
composer docs:openapi
```

With `L5_SWAGGER_GENERATE_ALWAYS=true` (see `.env.example`), the spec is rebuilt when you open the UI.

## URLs (same origin, nginx :8080)

| What | Path |
| --- | --- |
| Swagger UI | `/api/documentation` |
| OpenAPI JSON | `/docs` |
| Generated files | `storage/api-docs/api-docs.json`, `storage/api-docs/api-docs.yaml` |

Authorize in Swagger UI with **bearerAuth**: paste the JWT from `POST /api/auth/login` (no `Bearer ` prefix; the UI adds it).

## Contract vs implemented routes

The spec lists the assignment API (health, auth, outlets, reports). Only routes that exist in `routes/api/` will succeed when you use “Try it out”. Unimplemented paths still appear so the frontend and reviewers have a stable contract list.

When you add a real endpoint, update the matching class in `app/OpenApi/Paths/` and regenerate. Do not put OpenAPI attributes on controllers.
