<?php
require_once __DIR__ . '/includes/admin_auth.php';
$action = $_GET['action'] ?? 'list';

function save_cover_upload(array $file): ?string {
    if (!isset($file['tmp_name']) || !$file['tmp_name']) return null;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $name = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
    $dest = UPLOADS_PATH . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
    return $name;
}

$authors = db()->query('SELECT * FROM authors ORDER BY name')->fetchAll();

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $title = trim($_POST['title']);
    $slug = slugify($title);
    $author = trim($_POST['author']);
    $release = $_POST['release_date'] ?: null;
    $desc = trim($_POST['description']);
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $cover = save_cover_upload($_FILES['cover_image'] ?? []) ?? '';

    $stmt = db()->prepare('INSERT INTO mangas (title, slug, author, description, cover_image, release_date, is_featured, created_at, updated_at) VALUES (:t,:s,:a,:d,:c,:r,:f,NOW(),NOW())');
    $stmt->execute([':t'=>$title, ':s'=>$slug, ':a'=>$author, ':d'=>$desc, ':c'=>$cover, ':r'=>$release, ':f'=>$featured]);
    $mid = (int)db()->lastInsertId();

    $genres = $_POST['genres'] ?? [];
    $mg = db()->prepare('INSERT INTO manga_genres (manga_id, genre_id) VALUES (:m,:g)');
    foreach ($genres as $gid) { $mg->execute([':m'=>$mid, ':g'=>(int)$gid]); }

    header('Location: mangas.php');
    exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $id = (int)$_GET['id'];
    $title = trim($_POST['title']);
    $slug = slugify($title);
    $author = trim($_POST['author']);
    $release = $_POST['release_date'] ?: null;
    $desc = trim($_POST['description']);
    $featured = isset($_POST['is_featured']) ? 1 : 0;
    $cover = save_cover_upload($_FILES['cover_image'] ?? []);

    if ($cover) {
        $stmt = db()->prepare('UPDATE mangas SET title=:t, slug=:s, author=:a, description=:d, cover_image=:c, release_date=:r, is_featured=:f, updated_at=NOW() WHERE id=:id');
        $stmt->execute([':t'=>$title, ':s'=>$slug, ':a'=>$author, ':d'=>$desc, ':c'=>$cover, ':r'=>$release, ':f'=>$featured, ':id'=>$id]);
    } else {
        $stmt = db()->prepare('UPDATE mangas SET title=:t, slug=:s, author=:a, description=:d, release_date=:r, is_featured=:f, updated_at=NOW() WHERE id=:id');
        $stmt->execute([':t'=>$title, ':s'=>$slug, ':a'=>$author, ':d'=>$desc, ':r'=>$release, ':f'=>$featured, ':id'=>$id]);
    }

    db()->prepare('DELETE FROM manga_genres WHERE manga_id = :id')->execute([':id'=>$id]);
    $genres = $_POST['genres'] ?? [];
    $mg = db()->prepare('INSERT INTO manga_genres (manga_id, genre_id) VALUES (:m,:g)');
    foreach ($genres as $gid) { $mg->execute([':m'=>$id, ':g'=>(int)$gid]); }

    header('Location: mangas.php');
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    db()->prepare('DELETE FROM manga_genres WHERE manga_id=:id')->execute([':id'=>$id]);
    db()->prepare('DELETE FROM chapters WHERE manga_id=:id')->execute([':id'=>$id]);
    db()->prepare('DELETE FROM bookmarks WHERE manga_id=:id')->execute([':id'=>$id]);
    db()->prepare('DELETE FROM mangas WHERE id=:id')->execute([':id'=>$id]);
    header('Location: mangas.php');
    exit;
}

include __DIR__ . '/includes/header.php';
$genres = get_genres();

if ($action === 'new' || $action === 'edit') {
    $editing = $action === 'edit';
    $manga = [ 'title'=>'', 'author'=>'', 'description'=>'', 'cover_image'=>'', 'release_date'=>'', 'is_featured'=>0 ];
    $selected = [];
    if ($editing) {
        $id = (int)$_GET['id'];
        $stmt = db()->prepare('SELECT * FROM mangas WHERE id=:id');
        $stmt->execute([':id'=>$id]);
        $manga = $stmt->fetch();
        $gs = db()->prepare('SELECT genre_id FROM manga_genres WHERE manga_id=:id');
        $gs->execute([':id'=>$id]);
        $selected = array_column($gs->fetchAll(), 'genre_id');
    }
?>
<div class="card">
  <div class="card-body">
    <h3><?php echo $editing ? 'Edit Manga' : 'New Manga'; ?></h3>
    <form method="post" enctype="multipart/form-data" action="?action=<?php echo $editing ? 'edit&id='.(int)$manga['id'] : 'create'; ?>">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input class="form-control" name="title" value="<?php echo htmlspecialchars($manga['title']); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Author</label>
          <div class="input-group">
            <input class="form-control" name="author" value="<?php echo htmlspecialchars($manga['author']); ?>" list="authorList" required>
            <datalist id="authorList">
              <?php foreach ($authors as $a): ?>
                <option value="<?php echo htmlspecialchars($a['name']); ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Release Date</label>
          <input type="date" class="form-control" name="release_date" value="<?php echo htmlspecialchars($manga['release_date']); ?>">
        </div>
        <div class="col-md-6 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_featured" <?php echo $manga['is_featured']? 'checked':''; ?>>
            <label class="form-check-label">Featured</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" rows="4" name="description"><?php echo htmlspecialchars($manga['description']); ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Cover Image</label>
          <input type="file" class="form-control" name="cover_image" accept="image/*">
        </div>
        <div class="col-12">
          <label class="form-label">Genres</label>
          <div class="row">
            <?php foreach ($genres as $g): ?>
              <div class="col-6 col-md-4 col-lg-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="genres[]" value="<?php echo $g['id']; ?>" <?php echo in_array($g['id'], $selected) ? 'checked' : ''; ?>>
                  <label class="form-check-label"><?php echo htmlspecialchars($g['name']); ?></label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="mt-3"><button class="btn btn-primary" type="submit">Save</button> <a class="btn btn-secondary" href="mangas.php">Cancel</a></div>
    </form>
  </div>
</div>
<?php
} else {
    $rows = db()->query('SELECT * FROM mangas ORDER BY updated_at DESC, created_at DESC')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Mangas</h3>
  <a class="btn btn-primary" href="?action=new">Add Manga</a>
</div>
<div class="table-responsive">
<table class="table table-dark table-striped align-middle">
  <thead><tr><th>Cover</th><th>Title</th><th>Author</th><th>Updated</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($rows as $m): ?>
      <tr>
        <td style="width:64px"><img src="<?php echo UPLOADS_URL . '/' . htmlspecialchars($m['cover_image']); ?>" alt="" style="width:48px; height:64px; object-fit:cover;"></td>
        <td><?php echo htmlspecialchars($m['title']); ?></td>
        <td><?php echo htmlspecialchars($m['author']); ?></td>
        <td><?php echo htmlspecialchars($m['updated_at']); ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-secondary" href="chapters.php?manga_id=<?php echo (int)$m['id']; ?>">Chapters</a>
          <a class="btn btn-sm btn-warning" href="?action=edit&id=<?php echo (int)$m['id']; ?>">Edit</a>
          <a class="btn btn-sm btn-danger" href="?action=delete&id=<?php echo (int)$m['id']; ?>" onclick="return confirm('Delete this manga?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php }
include __DIR__ . '/includes/footer.php';
?>