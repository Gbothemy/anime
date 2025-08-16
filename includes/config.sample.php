<?php
// CrypyedManga - Sample Configuration
// Copy this file to config.php and fill in your credentials.

// Database
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'crypyedmanga');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site
define('SITE_NAME', 'CrypyedManga');
define('DEFAULT_LANGUAGE', 'en');

// Base URL (optional). If empty, it will be detected automatically.
define('BASE_URL', ''); // e.g. 'https://crypyedmanga.local' (no trailing slash)

// SMTP (optional). If not set, PHP's mail() will be attempted.
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', 'no-reply@crypyedmanga.local');
define('SMTP_FROM_NAME', 'CrypyedManga');

// MangaDex API
define('MANGADEX_BASE_API', 'https://api.mangadex.org');
define('MANGADEX_UPLOADS_BASE', 'https://uploads.mangadex.org');

// Import settings
// Number of mangas to import/update per run
define('IMPORT_LIMIT_PER_RUN', 5);
// Which language to import (MangaDex language code)
define('IMPORT_LANGUAGE', 'en');

// Timezone
define('APP_TIMEZONE', 'UTC');
@date_default_timezone_set(APP_TIMEZONE);

// Security
// Change this to a long random string in your config.php
define('APP_SECRET', 'change-me-please-very-long-random-string');