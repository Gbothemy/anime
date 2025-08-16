<?php
require_once __DIR__ . '/includes/admin_auth.php';
$mangaId = (int)($_GET['manga_id'] ?? 0);
if ($mangaId <= 0) { header('Location: mangas.php'); exit; }

$action = $_GET['action'] ?? 'list';

function store_chapter_images(int $chapterId, array $files): void {
    if (!isset($files['name'])) return;
    $count = count($files['name']);
    for ($i=0; $i<$count; $i++) {
        if (!$files['tmp_name'][$i]) continue;
        $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION) ?: 'jpg';
        $fname = 'c' . $chapterId . '_' . time() . '_' . $i . '_' . bin2hex(random_bytes(2)) . '.' . strtolower($ext);
        $rel = $fname;
        $dest = UPLOADS_PATH . '/' . $rel;
        if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
            $pageNumber = $i + 1;
            $stmt = db()->prepare('INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES (:cid, :p, :img)');
            $stmt->execute([':cid' => $chapterId, ':p' => $pageNumber, ':img' => $rel]);
        }
    }
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $number = $_POST['chapter_number'];
    $title = trim($_POST['title']);
    $stmt = db()->prepare('INSERT INTO chapters (manga_id, chapter_number, title, created_at) VALUES (:m,:n,:t,NOW())');
    $stmt->execute([':m'=>$mangaId, ':n'=>$number, ':t'=>$title]);
    $cid = (int)db()->lastInsertId();
    store_chapter_images($cid, $_FILES['images'] ?? []);
    db()->prepare('UPDATE mangas SET updated_at = NOW() WHERE id=:id')->execute([':id'=>$mangaId]);
    header('Location: chapters.php?manga_id=' . $mangaId);
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $cid = (int)$_GET['id'];
    db()->prepare('DELETE FROM chapter_images WHERE chapter_id=:id')->execute([':id'=>$cid]);
    db()->prepare('DELETE FROM chapters WHERE id=:id')->execute([':id'=>$cid]);
    db()->prepare('UPDATE mangas SET updated_at = NOW() WHERE id=:id')->execute([':id'=>$mangaId]);
    header('Location: chapters.php?manga_id=' . $mangaId);
    exit;
}

include __DIR__ . '/includes/header.php';
$manga = db()->prepare('SELECT * FROM mangas WHERE id=:id');
$manga->execute([':id'=>$mangaId]);
$manga = $manga->fetch();
$chapters = db()->prepare('SELECT * FROM chapters WHERE manga_id=:id ORDER BY chapter_number ASC');
$chapters->execute([':id'=>$mangaId]);
$chapters = $chapters->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Chapters — <?php echo htmlspecialchars($manga['title']); ?></h3>
  <a class="btn btn-secondary" href="mangas.php">Back</a>
</div>
<div class="card mb-3">
  <div class="card-body">
    <h5>Add Chapter</h5>
    <form method="post" enctype="multipart/form-data" action="?manga_id=<?php echo $mangaId; ?>&action=create">
      <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Chapter Number</label>
          <input class="form-control" type="text" name="chapter_number" required>
        </div>
        <div class="col-md-9">
          <label class="form-label">Title</label>
          <input class="form-control" type="text" name="title">
        </div>
        <div class="col-12">
          <label class="form-label">Images</label>
          <input class="form-control" type="file" name="images[]" accept="image/*" multiple required>
        </div>
      </div>
      <div class="mt-3"><button class="btn btn-primary" type="submit">Create</button></div>
    </form>
  </div>
</div>

<div class="table-responsive">
<table class="table table-dark table-striped align-middle">
  <thead><tr><th>#</th><th>Title</th><th>Pages</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($chapters as $c): ?>
    <?php $pages = db()->prepare('SELECT COUNT(1) FROM chapter_images WHERE chapter_id=:id'); $pages->execute([':id'=>$c['id']]); $pages = (int)$pages->fetchColumn(); ?>
    <tr>
      <td><?php echo htmlspecialchars($c['chapter_number']); ?></td>
      <td><?php echo htmlspecialchars($c['title']); ?></td>
      <td><?php echo $pages; ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-danger" href="?manga_id=<?php echo $mangaId; ?>&action=delete&id=<?php echo (int)$c['id']; ?>" onclick="return confirm('Delete this chapter?')">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>