<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_admin()) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
} else {
    header('Location: ' . BASE_URL . 'admin/login.php');
}
exit;