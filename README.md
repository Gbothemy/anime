# CrypyedManga (PHP + MySQL + Bootstrap 5)

Quick start

1. Create database and import schema
   - Create a MySQL database named `crypyedmanga`
   - Import `database.sql`

2. Configure DB and site
   - Copy `includes/config.sample.php` to `includes/config.php` and edit credentials
   - Optional: set `BASE_URL` if not auto-detecting, and `APP_SECRET` for cron

3. Run locally
   - Serve with Apache (with `.htaccess`) or PHP built-in: `php -S 0.0.0.0:8000 -t /workspace`

4. Login
   - Admin: `admin@example.com` / `password`
   - Demo: `demo@example.com` / `password`

5. Admin panel
   - `/admin/` (visible when logged in as admin)

6. Retriever & Upload
   - Admin > Manga Retriever to import from MangaDex (internet required) or upload a ZIP per chapter
   - CLI: `php /workspace/manga_retriever.php "<search query>"`

7. Cron import (every 24h)
   - CLI cron: `0 3 * * * php /absolute/path/cron_import.php >> /var/log/crypyedmanga_cron.log 2>&1`
   - Or HTTP: `https://your.site/cron_import.php?key=APP_SECRET`

SEO-friendly URLs

- Enabled via `.htaccess` (Apache):
  - `/manga/<slug>` -> `manga.php?slug=<slug>`
  - `/chapter/<slug>/<num>` -> `chapter.php?slug=<slug>&num=<num>`

File structure

- `assets/css`, `assets/js`, `assets/images`
- `includes/*` (config, db, helpers, header/footer)
- `admin/*` (panel)
- `uploads/mangas/*` (covers and chapter images)
- `manga_retriever.php`, `cron_import.php`
- `database.sql`