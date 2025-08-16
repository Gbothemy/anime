<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
require_login();

$userId = (int)current_user()['id'];
$sql = 'SELECT rh.*, m.title AS manga_title, m.slug AS manga_slug, c.chapter_number FROM reading_history rh JOIN mangas m ON m.id=rh.manga_id JOIN chapters c ON c.id=rh.chapter_id WHERE rh.user_id=:u ORDER BY rh.updated_at DESC LIMIT 100';
$rows = db_query($sql, [':u'=>$userId])->fetchAll();
?>
<h1 class="h5 mb-3">Reading History</h1>
<div class="list-group">
  <?php foreach ($rows as $r): ?>
    <a class="list-group-item list-group-item-action d-flex justify-content-between" href="<?php echo e(seo_url_chapter($r['manga_slug'], $r['chapter_number'])); ?>">
      <span><?php echo e($r['manga_title']); ?> • Chapter <?php echo e($r['chapter_number']); ?></span>
      <span class="text-muted small"><?php echo e(date('Y-m-d H:i', strtotime($r['updated_at']))); ?></span>
    </a>
  <?php endforeach; ?>
  <?php if (!$rows): ?><div class="list-group-item">No history yet.</div><?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>