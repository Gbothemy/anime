<?php
$mangaSlug = $_GET['manga'] ?? '';
$chapterNumber = $_GET['chapter'] ?? '';
if ($mangaSlug && $chapterNumber !== '') {
    header('Location: chapter.php?slug=' . urlencode($mangaSlug) . '&num=' . urlencode($chapterNumber), true, 301);
    exit;
}
http_response_code(404);
echo 'Not found';