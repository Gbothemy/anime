<?php
require_once __DIR__ . '/includes/admin_auth.php';
$success = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) die('Invalid CSRF');
    $mode = $_POST['mode'] ?? '';
    if ($mode === 'mangadex') {
        $mangaId = trim($_POST['mangadex_id'] ?? '');
        $lang = $_POST['language'] ?? 'en';
        $cmd = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/../scripts/manga_retriever.php') . ' --mangadex ' . escapeshellarg($mangaId) . ' --lang ' . escapeshellarg($lang);
        $output = shell_exec($cmd . ' 2>&1');
        $success = 'Retriever finished. Output: ' . htmlspecialchars($output);
    } elseif ($mode === 'upload') {
        if (!isset($_FILES['zip']) || !$_FILES['zip']['tmp_name']) {
            $error = 'No ZIP uploaded.';
        } else {
            $tmp = $_FILES['zip']['tmp_name'];
            $cmd = PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/../scripts/manga_retriever.php') . ' --upload ' . escapeshellarg($tmp);
            $output = shell_exec($cmd . ' 2>&1');
            $success = 'Upload processed. Output: ' . htmlspecialchars($output);
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<h3>Retriever / Upload</h3>
<p class="text-muted">MangaDex example ID: <code>15c2a63b-b48a-4580-88dc-1224f071f91d</code> (One Punch-Man). Use English language.</p>
<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h5>MangaDex Import</h5>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <input type="hidden" name="mode" value="mangadex">
          <div class="mb-3">
            <label class="form-label">MangaDex ID</label>
            <input class="form-control" name="mangadex_id" placeholder="UUID" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Language</label>
            <select class="form-select" name="language">
              <option value="en">English</option>
            </select>
          </div>
          <button class="btn btn-primary" type="submit">Fetch</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h5>Local ZIP Upload</h5>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
          <input type="hidden" name="mode" value="upload">
          <div class="mb-3">
            <label class="form-label">ZIP File</label>
            <input class="form-control" type="file" name="zip" accept=".zip" required>
          </div>
          <button class="btn btn-primary" type="submit">Process Upload</button>
        </form>
        <p class="small text-muted mt-2">ZIP structure: <code>manga_title/chap_1/*.jpg</code>, <code>manga_title/chap_2/*.png</code>, etc.</p>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>