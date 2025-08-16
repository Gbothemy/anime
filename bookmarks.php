<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Bookmarks';
$uid = current_user()['id'];
$sql = 'SELECT m.* FROM bookmarks b JOIN mangas m ON m.id = b.manga_id WHERE b.user_id = :uid ORDER BY b.created_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute([':uid' => $uid]);
$rows = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<h3>Your Bookmarks</h3>
<div class="row g-3">
<?php foreach ($rows as $m): ?>
  <div class="col-6 col-md-4 col-lg-3">
    <div class="card manga-card h-100">
      <img src="<?php echo UPLOADS_URL . '/' . htmlspecialchars($m['cover_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($m['title']); ?>">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-1" style="min-height:2.5rem;">
          <a class="stretched-link text-decoration-none" href="manga.php?slug=<?php echo urlencode($m['slug']); ?>"><?php echo htmlspecialchars($m['title']); ?></a>
        </h5>
        <form method="post" action="toggle_bookmark.php" class="mt-auto">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <input type="hidden" name="manga_id" value="<?php echo (int)$m['id']; ?>">
          <button class="btn btn-sm btn-outline-light" type="submit"><i class="bi bi-bookmark-fill"></i> Remove</button>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>