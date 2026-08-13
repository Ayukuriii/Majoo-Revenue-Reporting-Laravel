# Ubuntu VPS deployment

Same-origin production: nginx serves the built React SPA from `public/spa` and the Laravel API from `public/index.php` via PHP-FPM. Replace every `APP_DOMAIN` with the real hostname (example only: `majoo-revenue-reporting.codecat.space`).

`public/spa` is gitignored. Build the SPA on the server (or in CI) with `npm run build`. Never overwrite `public/index.php` with the SPA.

## 1. Server and DNS

- Ubuntu **22.04** or **24.04**
- DNS **A** (and **AAAA** if you use IPv6) for `APP_DOMAIN` pointing at the VPS public IP
- SSH access as a sudo user

Wait until DNS resolves before requesting a Let’s Encrypt certificate.

## 2. Packages

PHP 8.2 example (use 8.3 packages if that is what you install; then match the FPM socket in nginx):

```bash
sudo apt update
sudo apt install -y nginx mysql-server composer unzip git curl \
    php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml \
    php8.2-curl php8.2-zip php8.2-bcmath \
    certbot python3-certbot-nginx
```

Node.js 20:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

Confirm: `php -v`, `node -v`, `nginx -v`. Socket: `ls /run/php/` (expect `php8.2-fpm.sock` or `php8.3-fpm.sock`).

## 3. Application directory

```bash
sudo mkdir -p /var/www/html
sudo git clone <YOUR_REPO_URL> /var/www/html/majoo-revenue-reporting
# or rsync the project tree to that path
cd /var/www/html/majoo-revenue-reporting
```

If the nginx `root` in the prod example is not this path, change both `root` directives in the vhost to match.

Writable by PHP-FPM (`www-data`):

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

## 4. MySQL

```bash
sudo mysql
```

```sql
CREATE DATABASE majoo_revenue_reporting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'majoo'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON majoo_revenue_reporting.* TO 'majoo'@'localhost';
FLUSH PRIVILEGES;
```

## 5. Laravel `.env`

```bash
cd /var/www/html/majoo-revenue-reporting
cp .env.example .env
```

Set at least:

| Key | Production value |
| --- | --- |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://APP_DOMAIN` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` (not the local example `3307`) |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | from step 4 |
| `VITE_API_BASE_URL` | `/api` |
| `L5_SWAGGER_GENERATE_ALWAYS` | `false` |
| `L5_SWAGGER_CONST_HOST` | `${APP_URL}` |

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan jwt:secret
php artisan migrate --force
```

Demo seed (assignment reviewers / staging only; skip on a clean production database):

```bash
php artisan db:seed --force
```

That creates:

| Email                   | Password   | Merchant   | Outlets     |
| ----------------------- | ---------- | ---------- | ----------- |
| `merchant1@example.com` | `password` | Merchant 1 | ids 1 and 3 |
| `merchant2@example.com` | `password` | Merchant 2 | id 2        |

Use these on `https://APP_DOMAIN/login`. Do not keep this seed on a real production database.

## 6. SPA build

```bash
npm ci
npm run build
```

Confirm `public/spa/index.html` exists and `public/index.php` is still Laravel’s front controller.

## 7. nginx (HTTP first)

Copy the example and substitute the hostname and PHP socket:

```bash
sudo cp /var/www/html/majoo-revenue-reporting/deploy/nginx/majoo-revenue-reporting.prod.conf.example \
    /etc/nginx/sites-available/majoo-revenue-reporting
sudo sed -i 's/APP_DOMAIN/majoo-revenue-reporting.codecat.space/g' \
    /etc/nginx/sites-available/majoo-revenue-reporting
```

If FPM is not 8.2, edit `fastcgi_pass unix:/run/php/php8.2-fpm.sock`.

Keep **`listen 80`** so ACME HTTP-01 can succeed. The example already allows `/.well-known` (dot-files are denied except that prefix).

```bash
sudo ln -s /etc/nginx/sites-available/majoo-revenue-reporting /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo systemctl enable --now php8.2-fpm nginx
```

Smoke: `curl -sI http://APP_DOMAIN/api/health` should reach PHP (not 502).

## 8. TLS (Let’s Encrypt)

```bash
sudo certbot --nginx -d APP_DOMAIN
```

Certbot enables `listen 443 ssl` and writes certificate paths. Confirm HTTPS:

```bash
curl -sI https://APP_DOMAIN/api/health
sudo certbot renew --dry-run
```

Renewals use `certbot.timer`.

## 9. Laravel caches

```bash
cd /var/www/html/majoo-revenue-reporting
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

After any `.env` change, run `php artisan config:cache` again.

This assignment does not require a queue worker. `.env.example` uses `QUEUE_CONNECTION=database`; reports and auth are synchronous. If you later dispatch jobs, add a systemd unit for `php artisan queue:work`.

## 10. Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

Ports **22**, **80**, and **443** must be open.

## 11. Smoke checks

| Check | Expected |
| --- | --- |
| `https://APP_DOMAIN/api/health` | `{ "data": { "status": "ok" }, ... }` |
| `https://APP_DOMAIN/login` | SPA login (not a Laravel Blade page) |
| Login (`merchant1@example.com` / `password`) | Dashboard + Merchant 1 reports |
| `https://APP_DOMAIN/api/documentation` | Swagger if you keep L5-Swagger in production |

SPA calls **relative** `/api/...` on this origin. Do not set `VITE_API_BASE_URL` to a different host.

## 12. Operations

| Topic | Notes |
| --- | --- |
| nginx error log | `/var/log/nginx/error.log` |
| PHP-FPM | `journalctl -u php8.2-fpm` (or 8.3) |
| Laravel log | `storage/logs/laravel.log` |
| Maintenance | `php artisan down` / `php artisan up` |
| 502 Bad Gateway | FPM down, or socket path mismatch vs nginx |
| Permission errors | `storage` / `bootstrap/cache` not owned by `www-data` |
| Blank SPA / 404 on client routes | missing `npm run build`, or `location /` not rooted at `public/spa` |

Redeploy:

```bash
cd /var/www/html/majoo-revenue-reporting
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
sudo systemctl reload nginx
```
