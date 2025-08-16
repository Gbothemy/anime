<?php
require_once __DIR__ . '/includes/functions.php';
$mangaSlug = $_GET['manga'] ?? '';
$chapterNumber = $_GET['chapter'] ?? '';
$chapter = ($mangaSlug && $chapterNumber !== '') ? get_chapter_by_slug_and_number($mangaSlug, $chapterNumber) : null;
if (!$chapter) {
    http_response_code(404);
    echo 'Chapter not found';
    exit;
}
$images = get_chapter_images((int)$chapter['id']);
$adj = get_prev_next_chapter((int)$chapter['manga_id'], $chapter['chapter_number']);
$pageTitle = $chapter['manga_title'] . ' - Chapter ' . $chapter['chapter_number'];

if (is_logged_in()) {
    add_reading_history(current_user()['id'], (int)$chapter['manga_id'], (int)$chapter['id']);
}

include __DIR__ . '/includes/header.php';
?>
<div class="reader-toolbar py-2 mb-3">
  <div class="container d-flex justify-content-between align-items-center">
    <div>
      <a class="btn btn-outline-light btn-sm" href="manga.php?slug=<?php echo urlencode($chapter['manga_slug']); ?>"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="d-flex gap-2">
      <?php if ($adj['prev']): ?>
        <a class="btn btn-primary btn-sm" href="read.php?manga=<?php echo urlencode($chapter['manga_slug']); ?>&chapter=<?php echo urlencode($adj['prev']['chapter_number']); ?>">Prev</a>
      <?php endif; ?>
      <?php if ($adj['next']): ?>
        <a class="btn btn-primary btn-sm" href="read.php?manga=<?php echo urlencode($chapter['manga_slug']); ?>&chapter=<?php echo urlencode($adj['next']['chapter_number']); ?>">Next</a>
      <?php endif; ?>
      <button class="btn btn-outline-light btn-sm" id="readerModeToggle"><i class="bi bi-moon-stars"></i> Toggle Mode</button>
    </div>
  </div>
</div>

<div class="reader-container">
  <h4 class="mb-3"><?php echo htmlspecialchars($chapter['manga_title']); ?> — Chapter <?php echo htmlspecialchars($chapter['chapter_number']); ?> <?php if ($chapter['title']) echo '· ' . htmlspecialchars($chapter['title']); ?></h4>
  <?php foreach ($images as $img): ?>
    <img class="reader-image" src="<?php echo UPLOADS_URL . '/' . htmlspecialchars($img['image_path']); ?>" alt="Page <?php echo (int)$img['page_number']; ?>">
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>