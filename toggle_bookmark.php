<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        exit;
    }
    $mangaId = (int)($_POST['manga_id'] ?? 0);
    if ($mangaId > 0) {
        $state = toggle_bookmark(current_user()['id'], $mangaId);
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    exit;
}
http_response_code(405);
?>