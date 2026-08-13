# Majoo Revenue Reporting

Laravel 12 API + React SPA (same origin). The browser talks to relative `/api/...`; nginx serves both the SPA and PHP-FPM so there is no CORS.

Public HTTP is **nginx + PHP-FPM**, not `php artisan serve`.

| Env               | SPA                      | API                          |
| ----------------- | ------------------------ | ---------------------------- |
| Development (WSL) | `http://localhost:8080/` | `http://localhost:8080/api/` |
| Production        | `https://$APP_DOMAIN/`   | `https://$APP_DOMAIN/api/`   |

`APP_DOMAIN` is a placeholder. `codecat.space` is only an example. Likely future host: `majoo-revenue-reporting.codecat.space`.

## Setup

### Development (WSL)

Project path: `/var/www/html/majoo-revenue-reporting`

1. Copy `.env.example` to `.env` and set `APP_URL=http://localhost:8080` (already the example default).
2. Enable the nginx vhost:

```bash
sudo ln -s /var/www/html/majoo-revenue-reporting/deploy/nginx/majoo-revenue-reporting.dev.conf /etc/nginx/sites-available/majoo-revenue-reporting
sudo ln -s /etc/nginx/sites-available/majoo-revenue-reporting /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

3. Ensure PHP-FPM is running. The vhost uses `unix:/run/php/php8.2-fpm.sock`; change the socket in the conf if this machine uses another PHP version.
4. `npm install` then `npm run dev` (Vite on `127.0.0.1:5173`). Open [http://localhost:8080/login](http://localhost:8080/login). nginx proxies `/` to Vite and `/api` to Laravel (`/api/health`, `/api/documentation`).

Frontend always uses `VITE_API_BASE_URL=/api` (relative, same origin). Do not add CORS middleware for the SPA.

Production SPA files: `npm run build` writes to `public/spa` (`public/spa/index.html`). Do not overwrite `public/index.php` with the SPA. Seed login: `merchant1@example.com` / `password`.

### Production (Ubuntu VPS)

1. Copy `deploy/nginx/majoo-revenue-reporting.prod.conf.example` to a live nginx conf.
2. Replace `APP_DOMAIN` in `server_name` (and TLS paths) with the real hostname.
3. `npm run build` so assets land in `public/spa`.
4. Point nginx at PHP-FPM, keep `listen 80` so ACME can succeed, then `nginx -t` and reload.
5. Issue a Let’s Encrypt certificate (replace `APP_DOMAIN` with the real hostname, e.g. `majoo-revenue-reporting.codecat.space`):

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d APP_DOMAIN
```

Certbot enables `listen 443 ssl` and writes `ssl_certificate` paths. Renewals run via `certbot.timer` (`sudo certbot renew --dry-run` to verify).

## License

This project is a programming assignment built on Laravel, which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
