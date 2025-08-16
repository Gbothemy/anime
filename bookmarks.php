<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$userId = (int)current_user()['id'];
$rows = db_query('SELECT m.* FROM bookmarks b JOIN mangas m ON m.id=b.manga_id WHERE b.user_id=:u ORDER BY b.created_at DESC', [':u'=>$userId])->fetchAll();
?>
<h1 class="h5 mb-3">Your Bookmarks</h1>
<div class="row g-3">
  <?php foreach ($rows as $m): ?>
    <div class="col-6 col-md-3 col-lg-2">
      <div class="cm-card h-100">
        <a href="<?php echo e(seo_url_manga($m['slug'])); ?>" class="text-decoration-none">
          <img class="manga-cover" src="<?php echo e(base_url($m['cover_image'] ?: 'uploads/mangas/placeholder.svg')); ?>" alt="<?php echo e($m['title']); ?>">
          <div class="p-2">
            <div class="fw-semibold small text-truncate" title="<?php echo e($m['title']); ?>"><?php echo e($m['title']); ?></div>
          </div>
        </a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$rows): ?><div class="col-12"><div class="alert alert-info">No bookmarks yet.</div></div><?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>