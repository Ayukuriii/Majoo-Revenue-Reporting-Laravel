# Local setup (WSL / Ubuntu)

Same-origin development: nginx on **port 80** serves `http://majoo-revenue-reporting.test`. `/api` goes to PHP-FPM; `/` is proxied to Vite on `127.0.0.1:5173`.

Do not use `php artisan serve` as the public entrypoint. Do not add CORS for the SPA.

## Prerequisites

- PHP **8.2+** with extensions: `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `mysql` (pdo_mysql)
- PHP-FPM matching that version (`php8.2-fpm` or `php8.3-fpm`)
- Composer 2
- Node.js **20+** and npm
- nginx
- MySQL 8 (or MariaDB)

Project path assumed: `/var/www/html/majoo-revenue-reporting`.

## Hosts

```bash
echo '127.0.0.1 majoo-revenue-reporting.test' | sudo tee -a /etc/hosts
```

Vite already allows this host (`allowedHosts` and HMR `clientPort: 80` in `vite.config.ts`).

## Application

```bash
cd /var/www/html/majoo-revenue-reporting
cp .env.example .env
```

Edit `.env`:

- `APP_URL=http://majoo-revenue-reporting.test` (`.env.example` may still say `http://localhost:8080`; use the hostname above)
- MySQL: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. `.env.example` uses `DB_PORT=3307` for a local bind; use `3306` if MySQL listens on the default port
- Leave `VITE_API_BASE_URL=/api`

```bash
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
npm install
```

Seed users: `merchant1@example.com` / `password` and `merchant2@example.com` / `password`.

## nginx + PHP-FPM

The committed vhost is [deploy/nginx/majoo-revenue-reporting.dev.conf](../deploy/nginx/majoo-revenue-reporting.dev.conf): `listen 80`, `server_name majoo-revenue-reporting.test`, FastCGI `unix:/run/php/php8.2-fpm.sock`.

If this machine uses another PHP version, change the socket (for example `unix:/run/php/php8.3-fpm.sock`). Confirm with `ls /run/php/`.

```bash
sudo ln -s /var/www/html/majoo-revenue-reporting/deploy/nginx/majoo-revenue-reporting.dev.conf /etc/nginx/sites-available/majoo-revenue-reporting
sudo ln -s /etc/nginx/sites-available/majoo-revenue-reporting /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo systemctl enable --now php8.2-fpm   # or php8.3-fpm
```

If PHP-FPM runs as `www-data`, Laravel must be writable:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

## Run the SPA

```bash
npm run dev
```

Vite listens on `127.0.0.1:5173`. Open [http://majoo-revenue-reporting.test/login](http://majoo-revenue-reporting.test/login).

Production-like SPA on the same machine: `npm run build` writes `public/spa`. The **dev** vhost still proxies `/` to Vite; use the production nginx example only on a VPS (or a separate local vhost).

## Verify

| Check | Expected |
| --- | --- |
| `GET http://majoo-revenue-reporting.test/api/health` | JSON with `data.status` = `ok` |
| Login as merchant 1 | Dashboard shows Merchant 1 |
| Merchant report year=2026 month=11 | 30 days, omzet `0` on every day |
| Outlet report outlet 1, August 2026 | Seeded omzet; **2026-08-03** is `0` |
| Outlet picker (merchant 1) | Outlet 1 and Outlet 2 (ids 1 and 3), not merchant 2’s outlet |
| Swagger | [http://majoo-revenue-reporting.test/api/documentation](http://majoo-revenue-reporting.test/api/documentation) |

```bash
php artisan test
```

## Troubleshooting

- **502 / empty `/api`:** PHP-FPM not running, or socket path in the vhost does not match `/run/php/`.
- **Vite HMR / blank page:** `npm run dev` not running; or hosts file missing `majoo-revenue-reporting.test`.
- **419 / 401 on API:** missing `APP_KEY` or `JWT_SECRET`; regenerate with `key:generate` and `jwt:secret`.
- **Database connection refused:** wrong `DB_PORT` (3307 vs 3306) or MySQL not listening.
- **Permission denied writing logs:** `storage` / `bootstrap/cache` not writable by the FPM user.
