<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

// Genres
if (isset($_POST['new_genre'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/genres.php')); }
    $name = trim($_POST['new_genre']);
    if ($name) { db_query('INSERT INTO genres (name) VALUES (:n) ON DUPLICATE KEY UPDATE name=VALUES(name)', [':n'=>$name]); }
    redirect(base_url('admin/genres.php'));
}
if (isset($_GET['del_genre'])) {
    if (!verify_csrf($_GET['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/genres.php')); }
    db_query('DELETE FROM genres WHERE id=:id', [':id'=>(int)$_GET['del_genre']]);
    redirect(base_url('admin/genres.php'));
}

// Authors
if (isset($_POST['new_author'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/genres.php')); }
    $name = trim($_POST['new_author']);
    if ($name) { db_query('INSERT INTO authors (name) VALUES (:n) ON DUPLICATE KEY UPDATE name=VALUES(name)', [':n'=>$name]); }
    redirect(base_url('admin/genres.php'));
}
if (isset($_GET['del_author'])) {
    if (!verify_csrf($_GET['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/genres.php')); }
    db_query('DELETE FROM authors WHERE id=:id', [':id'=>(int)$_GET['del_author']]);
    redirect(base_url('admin/genres.php'));
}

$genres = db_query('SELECT * FROM genres ORDER BY name')->fetchAll();
$authors = db_query('SELECT * FROM authors ORDER BY name')->fetchAll();
?>
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="cm-card p-3">
      <h1 class="h6">Genres</h1>
      <form class="d-flex gap-2 mb-3" method="post">
        <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
        <input class="form-control" name="new_genre" placeholder="Add genre">
        <button class="btn btn-gradient btn-sm" type="submit">Add</button>
      </form>
      <ul class="list-group list-group-flush">
        <?php foreach ($genres as $g): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?php echo e($g['name']); ?></span>
            <a class="btn btn-sm btn-outline-danger" href="?del_genre=<?php echo (int)$g['id']; ?>&csrf=<?php echo e(csrf_token()); ?>" onclick="return confirm('Delete this genre?')">Delete</a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="cm-card p-3">
      <h1 class="h6">Authors</h1>
      <form class="d-flex gap-2 mb-3" method="post">
        <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
        <input class="form-control" name="new_author" placeholder="Add author">
        <button class="btn btn-gradient btn-sm" type="submit">Add</button>
      </form>
      <ul class="list-group list-group-flush">
        <?php foreach ($authors as $a): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?php echo e($a['name']); ?></span>
            <a class="btn btn-sm btn-outline-danger" href="?del_author=<?php echo (int)$a['id']; ?>&csrf=<?php echo e(csrf_token()); ?>" onclick="return confirm('Delete this author?')">Delete</a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>