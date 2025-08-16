<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$mangaId = (int)($_GET['manga_id'] ?? 0);
$manga = $mangaId ? db_query('SELECT * FROM mangas WHERE id=:id', [':id'=>$mangaId])->fetch() : null;

$action = $_GET['action'] ?? 'list';

if ($action === 'delete' && isset($_GET['id'])) {
    if (!verify_csrf($_GET['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/chapters.php?manga_id='.$mangaId)); }
    db_query('DELETE FROM chapters WHERE id=:id', [':id'=>(int)$_GET['id']]);
    $_SESSION['flash']=['type'=>'success','msg'=>'Chapter deleted'];
    redirect(base_url('admin/chapters.php?manga_id='.$mangaId));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid CSRF']; redirect(base_url('admin/chapters.php?manga_id='.$mangaId)); }
    $id = (int)($_POST['id'] ?? 0);
    $chapterNumber = trim($_POST['chapter_number'] ?? '');
    $title = trim($_POST['title'] ?? '');

    if ($id > 0) {
        db_query('UPDATE chapters SET chapter_number=:n, title=:t WHERE id=:id', [':n'=>$chapterNumber, ':t'=>$title, ':id'=>$id]);
        $chapterId = $id;
    } else {
        db_query('INSERT INTO chapters (manga_id, chapter_number, title, upload_source) VALUES (:m,:n,:t,\'local\')', [':m'=>$mangaId, ':n'=>$chapterNumber, ':t'=>$title]);
        $chapterId = (int)db_last_insert_id();
    }

    if (!empty($_FILES['images'])) {
        $count = count($_FILES['images']['name']);
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name'=>$_FILES['images']['name'][$i],
                'type'=>$_FILES['images']['type'][$i],
                'tmp_name'=>$_FILES['images']['tmp_name'][$i],
                'error'=>$_FILES['images']['error'][$i],
                'size'=>$_FILES['images']['size'][$i],
            ];
            $saved = save_uploaded_file($file, __DIR__ . '/../uploads/mangas', ['image/jpeg','image/png','image/webp']);
            if ($saved) {
                $path = 'uploads/mangas/' . basename($saved);
                $page = (int)db_query('SELECT COALESCE(MAX(page_number),0)+1 FROM chapter_images WHERE chapter_id=:c', [':c'=>$chapterId])->fetchColumn();
                db_query('INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES (:c,:p,:path)', [':c'=>$chapterId, ':p'=>$page, ':path'=>$path]);
            }
        }
        $pages = (int)db_query('SELECT COUNT(*) FROM chapter_images WHERE chapter_id=:c', [':c'=>$chapterId])->fetchColumn();
        db_query('UPDATE chapters SET pages_count=:pc WHERE id=:id', [':pc'=>$pages, ':id'=>$chapterId]);
    }

    $_SESSION['flash']=['type'=>'success','msg'=>'Chapter saved'];
    redirect(base_url('admin/chapters.php?manga_id='.$mangaId));
}

$editing = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $editing = db_query('SELECT * FROM chapters WHERE id=:id', [':id'=>(int)$_GET['id']])->fetch();
}

$chapters = $mangaId ? db_query('SELECT * FROM chapters WHERE manga_id=:m ORDER BY CAST(chapter_number AS DECIMAL(10,3)) DESC', [':m'=>$mangaId])->fetchAll() : [];
?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h5 mb-0">Chapters <?php if ($manga): ?><span class="text-muted">for <?php echo e($manga['title']); ?></span><?php endif; ?></h1>
  <button class="btn btn-sm btn-gradient" data-bs-toggle="collapse" data-bs-target="#chapterForm">Add Chapter</button>
</div>
<div class="collapse <?php echo $editing?'show':''; ?>" id="chapterForm">
  <div class="cm-card p-3 mb-3">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
      <input type="hidden" name="id" value="<?php echo e($editing['id'] ?? 0); ?>">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Chapter Number</label>
          <input class="form-control" name="chapter_number" value="<?php echo e($editing['chapter_number'] ?? ''); ?>" required>
        </div>
        <div class="col-md-8">
          <label class="form-label">Title</label>
          <input class="form-control" name="title" value="<?php echo e($editing['title'] ?? ''); ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Images (multiple)</label>
          <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
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
    <table class="table table-dark align-middle mb-0">
      <thead><tr><th>Chapter</th><th>Title</th><th>Pages</th><th>Updated</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($chapters as $c): ?>
          <tr>
            <td>#<?php echo e($c['chapter_number']); ?></td>
            <td><?php echo e($c['title'] ?: '—'); ?></td>
            <td><?php echo (int)$c['pages_count']; ?></td>
            <td class="text-muted small"><?php echo e($c['updated_at']); ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-info" href="?manga_id=<?php echo (int)$mangaId; ?>&action=edit&id=<?php echo (int)$c['id']; ?>">Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="?manga_id=<?php echo (int)$mangaId; ?>&action=delete&id=<?php echo (int)$c['id']; ?>&csrf=<?php echo e(csrf_token()); ?>" onclick="return confirm('Delete this chapter?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$chapters): ?><tr><td colspan="5" class="text-center py-4">No chapters yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>