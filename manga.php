<?php
require_once __DIR__ . '/includes/functions.php';
$slug = $_GET['slug'] ?? '';
$manga = $slug ? get_manga_by_slug($slug) : null;
if (!$manga) {
    http_response_code(404);
    echo 'Manga not found';
    exit;
}
$pageTitle = $manga['title'];
include __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
  <div class="col-12 col-md-3">
    <img src="<?php echo UPLOADS_URL . '/' . htmlspecialchars($manga['cover_image']); ?>" alt="<?php echo htmlspecialchars($manga['title']); ?>" class="img-fluid rounded">
  </div>
  <div class="col-12 col-md-9">
    <h2 class="mb-2"><?php echo htmlspecialchars($manga['title']); ?></h2>
    <div class="manga-meta mb-2">Author: <?php echo htmlspecialchars($manga['author']); ?> • Released: <?php echo htmlspecialchars($manga['release_date']); ?></div>
    <div class="mb-3">
      <?php foreach ($manga['genres'] as $g): ?>
        <a class="badge bg-secondary text-decoration-none" href="manga_list.php?genre=<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></a>
      <?php endforeach; ?>
    </div>
    <p class="mb-3"><?php echo nl2br(htmlspecialchars($manga['description'])); ?></p>
    <?php if (is_logged_in()): ?>
      <form method="post" action="toggle_bookmark.php" class="mb-3">
        <input type="hidden" name="manga_id" value="<?php echo (int)$manga['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        <?php $bookmarked = is_bookmarked(current_user()['id'], (int)$manga['id']); ?>
        <button class="btn btn-outline-light" type="submit">
          <i class="bi bi-bookmark<?php echo $bookmarked ? '-fill' : ''; ?>"></i> <?php echo $bookmarked ? 'Bookmarked' : 'Bookmark'; ?>
        </button>
      </form>
    <?php endif; ?>
    <h4>Chapters</h4>
    <div class="list-group">
      <?php foreach (get_manga_chapters((int)$manga['id']) as $c): ?>
        <a class="list-group-item list-group-item-action" href="<?php echo url_read($manga['slug'], $c['chapter_number']); ?>">
          Chapter <?php echo htmlspecialchars($c['chapter_number']); ?> — <?php echo htmlspecialchars($c['title']); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>