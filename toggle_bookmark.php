<?php
require_once __DIR__ . '/includes/init.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        exit;
    }
    $mangaId = (int)($_POST['manga_id'] ?? 0);
    if ($mangaId > 0) {
        $state = toggle_bookmark((int)current_user()['id'], $mangaId);
    }
    redirect($_SERVER['HTTP_REFERER'] ?? base_url('/'));
}
http_response_code(405);
?>