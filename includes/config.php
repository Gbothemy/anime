<?php
// Core configuration for Manga Reader website

// Env helpers
function envv(string $key, $default = null) {
    $v = getenv($key);
    return $v === false ? $default : $v;
}

// Database
define('DB_HOST', envv('DB_HOST', 'localhost'));
define('DB_NAME', envv('DB_NAME', 'manga_reader'));
define('DB_USER', envv('DB_USER', 'root'));
define('DB_PASS', envv('DB_PASS', ''));

// App
define('SITE_NAME', envv('SITE_NAME', 'MangaReader'));
define('BASE_URL', envv('BASE_URL', '/'));

define('BASE_PATH', dirname(__DIR__));
define('ASSETS_URL', BASE_URL . 'assets');
define('UPLOADS_URL', BASE_URL . 'uploads/mangas');
define('UPLOADS_PATH', BASE_PATH . '/uploads/mangas');

// Email
define('MAIL_FROM', envv('MAIL_FROM', 'no-reply@mangareader.local'));
define('MAIL_TO_ADMIN', envv('MAIL_TO_ADMIN', 'admin@mangareader.local'));

// Security
define('SESSION_NAME', 'manga_reader_sess');
define('DEBUG', filter_var(envv('DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN));

// Timezone
date_default_timezone_set('UTC');

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');

// Common constants
define('ITEMS_PER_PAGE', 12);
?>