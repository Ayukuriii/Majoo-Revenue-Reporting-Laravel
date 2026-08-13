# Majoo Revenue Reporting

Laravel 12 API + React 19 SPA on the **same origin**. The browser calls relative `/api/...`. Public HTTP is **nginx + PHP-FPM**, not `php artisan serve`. There is no CORS middleware for the SPA.

| Env               | SPA                                    | API                                        |
| ----------------- | -------------------------------------- | ------------------------------------------ |
| Development (WSL) | `http://majoo-revenue-reporting.test/` | `http://majoo-revenue-reporting.test/api/` |
| Production        | `https://$APP_DOMAIN/`                 | `https://$APP_DOMAIN/api/`                 |

`APP_DOMAIN` is a placeholder. `codecat.space` is only an example. Likely future host: `majoo-revenue-reporting.codecat.space`.

Add to `/etc/hosts` locally: `127.0.0.1 majoo-revenue-reporting.test`.

## Stack

- PHP **8.2+**, Laravel **12**, MySQL, JWT (`php-open-source-saver/jwt-auth`)
- React **19** + Vite + Tailwind (SPA in `spa/`, production build in `public/spa`)
- nginx + PHP-FPM (configs in `deploy/nginx/`)

`.env.example` uses `DB_PORT=3307`. That port is **local-specific**; production MySQL is usually `3306`. Set `APP_URL` to the origin you actually use (`http://majoo-revenue-reporting.test` locally). Frontend always uses `VITE_API_BASE_URL=/api`.

Do not run `composer run dev`: that Composer script still starts `php artisan serve`. Use nginx + `npm run dev` instead.

## Seed logins

After `php artisan migrate --seed`:

| Email                   | Password   | Merchant   | Outlets     |
| ----------------------- | ---------- | ---------- | ----------- |
| `merchant1@example.com` | `password` | Merchant 1 | ids 1 and 3 |
| `merchant2@example.com` | `password` | Merchant 2 | id 2        |

Tenancy is the JWT user → `merchants.user_id`. The API never trusts a client-supplied `merchant_id`. Seed transactions are **August 2026** only, so a November merchant report is all zeros (calendar fill). Omzet = `SUM(bill_total)`.

## Quick start (local)

Full steps: [docs/LOCAL_SETUP.md](docs/LOCAL_SETUP.md).

```bash
cp .env.example .env
# Set APP_URL=http://majoo-revenue-reporting.test and MySQL credentials
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
npm install
```

Enable the nginx vhost (`deploy/nginx/majoo-revenue-reporting.dev.conf` → port 80, `server_name majoo-revenue-reporting.test`), then:

```bash
npm run dev
```

Open [http://majoo-revenue-reporting.test/login](http://majoo-revenue-reporting.test/login). nginx proxies `/` to Vite (`127.0.0.1:5173`) and `/api` to Laravel.

Production SPA: `npm run build` writes `public/spa/index.html`. Do **not** overwrite `public/index.php`.

## Production (Ubuntu VPS)

See **[docs/DEPLOYMENT_VPS.md](docs/DEPLOYMENT_VPS.md)** for packages, MySQL, PHP-FPM, nginx, Let’s Encrypt, caches, and redeploy.

Summary: copy `deploy/nginx/majoo-revenue-reporting.prod.conf.example`, replace `APP_DOMAIN`, keep `listen 80` until Certbot, `npm run build`, point PHP-FPM at `public/index.php`, SPA root at `public/spa`.

## API

| Method | Path                                                          | Auth           |
| ------ | ------------------------------------------------------------- | -------------- |
| `GET`  | `/api/health`                                                 | no             |
| `POST` | `/api/auth/login`                                             | no (throttled) |
| `POST` | `/api/auth/logout`                                            | JWT            |
| `POST` | `/api/auth/refresh`                                           | JWT            |
| `GET`  | `/api/auth/me`                                                | JWT            |
| `GET`  | `/api/outlets`                                                | JWT            |
| `GET`  | `/api/reports/merchant?year=&month=&page=&per_page=`          | JWT            |
| `GET`  | `/api/reports/outlet?outlet_id=&year=&month=&page=&per_page=` | JWT            |

Swagger UI: `/api/documentation`. Contract: [docs/OPENAPI.md](docs/OPENAPI.md).

## Tests

```bash
php artisan test
# or
composer test
```

Pest Feature tests cover login, tenancy (cross-merchant outlet → 403), and monthly reports (November zeros, August seed totals).

## Docs

| Doc                                                  | Contents                                             |
| ---------------------------------------------------- | ---------------------------------------------------- |
| [docs/LOCAL_SETUP.md](docs/LOCAL_SETUP.md)           | WSL/local nginx, hosts, env, permissions             |
| [docs/DEPLOYMENT_VPS.md](docs/DEPLOYMENT_VPS.md)     | Ubuntu VPS from packages through TLS                 |
| [docs/OPENAPI.md](docs/OPENAPI.md)                   | Generate Swagger / OpenAPI                           |
| [docs/DML_AND_INDEXING.md](docs/DML_AND_INDEXING.md) | Parameterized DML, report indexes, decimal vs double |

## License

This project is a programming assignment built on Laravel, which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
