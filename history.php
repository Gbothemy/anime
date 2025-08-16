<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Reading History';
$uid = current_user()['id'];
$sql = 'SELECT rh.*, m.title, m.slug, c.chapter_number FROM reading_history rh JOIN mangas m ON m.id = rh.manga_id JOIN chapters c ON c.id = rh.chapter_id WHERE rh.user_id = :uid ORDER BY rh.last_read_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute([':uid' => $uid]);
$rows = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<h3>Reading History</h3>
<div class="list-group">
<?php foreach ($rows as $r): ?>
  <a class="list-group-item list-group-item-action" href="read.php?manga=<?php echo urlencode($r['slug']); ?>&chapter=<?php echo urlencode($r['chapter_number']); ?>">
    <div class="d-flex w-100 justify-content-between">
      <h5 class="mb-1"><?php echo htmlspecialchars($r['title']); ?> — Chapter <?php echo htmlspecialchars($r['chapter_number']); ?></h5>
      <small class="text-muted"><?php echo htmlspecialchars($r['last_read_at']); ?></small>
    </div>
  </a>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>