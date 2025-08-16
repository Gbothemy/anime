<?php
require_once __DIR__ . '/includes/admin_auth.php';
$stats = [
  'mangas' => (int)db()->query('SELECT COUNT(1) FROM mangas')->fetchColumn(),
  'chapters' => (int)db()->query('SELECT COUNT(1) FROM chapters')->fetchColumn(),
  'messages' => (int)db()->query('SELECT COUNT(1) FROM messages')->fetchColumn(),
  'users' => (int)db()->query('SELECT COUNT(1) FROM users')->fetchColumn(),
];
include __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
  <div class="col-6 col-lg-3">
    <div class="card"><div class="card-body text-center"><div class="display-6"><?php echo $stats['mangas']; ?></div><div>Mangas</div></div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card"><div class="card-body text-center"><div class="display-6"><?php echo $stats['chapters']; ?></div><div>Chapters</div></div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card"><div class="card-body text-center"><div class="display-6"><?php echo $stats['users']; ?></div><div>Users</div></div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card"><div class="card-body text-center"><div class="display-6"><?php echo $stats['messages']; ?></div><div>Messages</div></div></div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>