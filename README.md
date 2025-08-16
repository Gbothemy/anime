# MangaReader (PHP + MySQL + Bootstrap)

Quick start

1. Create database and import schema
   - Create a MySQL database named `manga_reader`
   - Import `database.sql`

2. Configure DB credentials
   - Edit `includes/config.php` or set environment variables (`.env` style)

3. Run locally
   - Serve the folder with PHP built-in server or Apache/Nginx
   - PHP built-in (if available): `php -S 0.0.0.0:8000 -t /workspace`

4. Login
   - Admin: `admin@example.com` / `password`
   - User: `user1@example.com` / `password`

5. Admin panel
   - `/admin/login.php` then go to Dashboard

6. Retriever & Upload
   - Admin > Retriever/Upload to import from MangaDex (internet required) or upload a ZIP.
   - CLI: `php scripts/manga_retriever.php --mangadex <uuid> --lang en`
          `php scripts/manga_retriever.php --upload /absolute/path/to/file.zip`

SEO-friendly URLs

- Enabled via `.htaccess` rules (Apache):
  - `/manga/<slug>` -> `manga.php?slug=<slug>`
  - `/read/<slug>/chapter/<num>` -> `read.php?manga=<slug>&chapter=<num>`

File structure

- `assets/css`, `assets/js`, `assets/images/mangas`
- `includes/*` (config/db/auth/helpers)
- `admin/*` (panel)
- `uploads/mangas/*` (covers and chapter images)
- `scripts/manga_retriever.php`
- `database.sql`