<?php
require_once __DIR__ . '/includes/admin_auth.php';
$action = $_GET['action'] ?? 'list';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $name = trim($_POST['name']);
    $slug = slugify($name);
    db()->prepare('INSERT INTO authors (name, slug) VALUES (:n,:s)')->execute([':n'=>$name, ':s'=>$slug]);
    header('Location: authors.php');
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $id = (int)$_GET['id'];
    $name = trim($_POST['name']);
    $slug = slugify($name);
    db()->prepare('UPDATE authors SET name=:n, slug=:s WHERE id=:id')->execute([':n'=>$name, ':s'=>$slug, ':id'=>$id]);
    header('Location: authors.php');
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    db()->prepare('DELETE FROM authors WHERE id=:id')->execute([':id'=>$id]);
    header('Location: authors.php');
    exit;
}

include __DIR__ . '/includes/header.php';
$rows = db()->query('SELECT * FROM authors ORDER BY name ASC')->fetchAll();
?>
<div class="row">
  <div class="col-12 col-lg-5">
    <div class="card mb-3">
      <div class="card-body">
        <h5>Add Author</h5>
        <form method="post" action="?action=create">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <div class="input-group">
            <input class="form-control" name="name" placeholder="Author Name" required>
            <button class="btn btn-primary" type="submit">Add</button>
          </div>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-body">
        <h5>Authors</h5>
        <div class="list-group">
          <?php foreach ($rows as $a): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong><?php echo htmlspecialchars($a['name']); ?></strong>
                <div class="small text-muted"><?php echo htmlspecialchars($a['slug']); ?></div>
              </div>
              <div>
                <a class="btn btn-sm btn-warning" href="?action=edit_form&id=<?php echo (int)$a['id']; ?>">Edit</a>
                <a class="btn btn-sm btn-danger" href="?action=delete&id=<?php echo (int)$a['id']; ?>" onclick="return confirm('Delete this author?')">Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-7">
    <?php if ($action === 'edit_form' && isset($_GET['id'])): $id = (int)$_GET['id']; $a = db()->prepare('SELECT * FROM authors WHERE id=:id'); $a->execute([':id'=>$id]); $a=$a->fetch(); ?>
    <div class="card">
      <div class="card-body">
        <h5>Edit Author</h5>
        <form method="post" action="?action=edit&id=<?php echo $id; ?>">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="<?php echo htmlspecialchars($a['name']); ?>" required>
          </div>
          <button class="btn btn-primary" type="submit">Save</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>