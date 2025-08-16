<?php
require_once __DIR__ . '/includes/admin_auth.php';
$action = $_GET['action'] ?? 'list';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $name = trim($_POST['name']);
    $slug = slugify($name);
    db()->prepare('INSERT INTO genres (name, slug) VALUES (:n,:s)')->execute([':n'=>$name, ':s'=>$slug]);
    header('Location: genres.php');
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $id = (int)$_GET['id'];
    $name = trim($_POST['name']);
    $slug = slugify($name);
    db()->prepare('UPDATE genres SET name=:n, slug=:s WHERE id=:id')->execute([':n'=>$name, ':s'=>$slug, ':id'=>$id]);
    header('Location: genres.php');
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    db()->prepare('DELETE FROM manga_genres WHERE genre_id=:id')->execute([':id'=>$id]);
    db()->prepare('DELETE FROM genres WHERE id=:id')->execute([':id'=>$id]);
    header('Location: genres.php');
    exit;
}

include __DIR__ . '/includes/header.php';
$rows = db()->query('SELECT * FROM genres ORDER BY name ASC')->fetchAll();
?>
<div class="row">
  <div class="col-12 col-lg-5">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Add Genre</h5>
        <form method="post" action="?action=create">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <div class="input-group">
            <input class="form-control" name="name" placeholder="Genre Name" required>
            <button class="btn btn-primary" type="submit">Add</button>
          </div>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-body">
        <h5>Genres</h5>
        <div class="list-group">
          <?php foreach ($rows as $g): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong><?php echo htmlspecialchars($g['name']); ?></strong>
                <div class="small text-muted"><?php echo htmlspecialchars($g['slug']); ?></div>
              </div>
              <div>
                <a class="btn btn-sm btn-warning" href="?action=edit_form&id=<?php echo (int)$g['id']; ?>">Edit</a>
                <a class="btn btn-sm btn-danger" href="?action=delete&id=<?php echo (int)$g['id']; ?>" onclick="return confirm('Delete this genre?')">Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-7">
    <?php if ($action === 'edit_form' && isset($_GET['id'])): $id = (int)$_GET['id']; $g = db()->prepare('SELECT * FROM genres WHERE id=:id'); $g->execute([':id'=>$id]); $g=$g->fetch(); ?>
    <div class="card">
      <div class="card-body">
        <h5>Edit Genre</h5>
        <form method="post" action="?action=edit&id=<?php echo $id; ?>">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="<?php echo htmlspecialchars($g['name']); ?>" required>
          </div>
          <button class="btn btn-primary" type="submit">Save</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>