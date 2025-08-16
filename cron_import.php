<?php
// To run via cron: php /path/to/cron_import.php
// Or via web (optional) with a secret: /cron_import.php?key=YOUR_SECRET
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/manga_retriever.php';

if (php_sapi_name() !== 'cli') {
    if (($_GET['key'] ?? '') !== APP_SECRET) {
        http_response_code(403); echo 'Forbidden'; exit;
    }
}

$list = import_from_mangadex('');
echo 'Imported/Updated: ' . implode(', ', $list) . PHP_EOL;