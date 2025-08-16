<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'delete' && isset($_GET['id'])) {
    if (!verify_csrf($_GET['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/mangas.php')); }
    db_query('DELETE FROM mangas WHERE id=:id', [':id'=>(int)$_GET['id']]);
    $_SESSION['flash']=['type'=>'success','msg'=>'Manga deleted'];
    redirect(base_url('admin/mangas.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/mangas.php')); }
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? slugify($title));
    $authorName = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $release = $_POST['release_date'] ?? null;
    if ($authorName) {
        $author = db_query('SELECT id FROM authors WHERE name=:n', [':n'=>$authorName])->fetch();
        if (!$author) { db_query('INSERT INTO authors (name) VALUES (:n)', [':n'=>$authorName]); $authorId = (int)db_last_insert_id(); }
        else { $authorId = (int)$author['id']; }
    } else { $authorId = null; }

    $coverPath = null;
    if (!empty($_FILES['cover']['name'])) {
        $cover = save_uploaded_file($_FILES['cover'], __DIR__ . '/../uploads/mangas', ['image/jpeg','image/png','image/webp','image/svg+xml']);
        if ($cover) { $coverPath = 'uploads/mangas/' . basename($cover); }
    }

    if ($id > 0) {
        db_query('UPDATE mangas SET title=:t, slug=:s, author_id=:a, description=:d, release_date=:r' . ($coverPath?', cover_image=:c':'') . ' WHERE id=:id', [
            ':t'=>$title, ':s'=>$slug, ':a'=>$authorId, ':d'=>$description, ':r'=>$release, ':id'=>$id
        ] + ($coverPath?[':c'=>$coverPath]:[]));
        $_SESSION['flash']=['type'=>'success','msg'=>'Manga updated'];
    } else {
        db_query('INSERT INTO mangas (title, slug, author_id, description, release_date, cover_image) VALUES (:t,:s,:a,:d,:r,:c)', [
            ':t'=>$title, ':s'=>$slug, ':a'=>$authorId, ':d'=>$description, ':r'=>$release, ':c'=>$coverPath ?: 'uploads/mangas/placeholder.svg'
        ]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Manga created'];
    }
    redirect(base_url('admin/mangas.php'));
}

$editing = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editing = db_query('SELECT * FROM mangas WHERE id=:id', [':id'=>(int)$_GET['id']])->fetch();
}

$rows = db_query('SELECT m.*, a.name AS author_name FROM mangas m LEFT JOIN authors a ON a.id=m.author_id ORDER BY m.updated_at DESC')->fetchAll();
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h5 mb-0">Mangas</h1>
  <button class="btn btn-sm btn-gradient" data-bs-toggle="collapse" data-bs-target="#mangaForm">Add Manga</button>
</div>
<div class="collapse <?php echo $editing?'show':''; ?>" id="mangaForm">
  <div class="cm-card p-3 mb-3">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
      <input type="hidden" name="id" value="<?php echo e($editing['id'] ?? 0); ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input class="form-control" name="title" value="<?php echo e($editing['title'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Slug</label>
          <input class="form-control" name="slug" value="<?php echo e($editing['slug'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Author</label>
          <input class="form-control" name="author" value="<?php echo e($editing['author_name'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Release Date</label>
          <input type="date" class="form-control" name="release_date" value="<?php echo e($editing['release_date'] ?? ''); ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" rows="4" name="description"><?php echo e($editing['description'] ?? ''); ?></textarea>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Cover Image</label>
          <input type="file" class="form-control" name="cover" accept="image/*">
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-gradient" type="submit"><?php echo $editing?'Update':'Create'; ?></button>
      </div>
    </form>
  </div>
</div>

<div class="cm-card p-0">
  <div class="table-responsive">
    <table class="table align-middle mb-0 table-dark">
      <thead><tr><th>Cover</th><th>Title</th><th>Author</th><th>Updated</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $m): ?>
          <tr>
            <td><img src="<?php echo e(base_url($m['cover_image'] ?: 'uploads/mangas/placeholder.svg')); ?>" width="44" height="58" style="object-fit:cover;border-radius:4px"></td>
            <td><a class="text-decoration-none" href="<?php echo e(seo_url_manga($m['slug'])); ?>"><?php echo e($m['title']); ?></a></td>
            <td><?php echo e($m['author_name'] ?: '—'); ?></td>
            <td class="text-muted small"><?php echo e($m['updated_at']); ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-info" href="?action=edit&id=<?php echo (int)$m['id']; ?>">Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?php echo (int)$m['id']; ?>&csrf=<?php echo e(csrf_token()); ?>" onclick="return confirm('Delete this manga?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>