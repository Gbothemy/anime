<?php
function url_manga(string $slug): string { return BASE_URL . 'manga/' . rawurlencode($slug); }
function url_read(string $slug, $chapter): string { return BASE_URL . 'read/' . rawurlencode($slug) . '/chapter/' . rawurlencode((string)$chapter); }
?>