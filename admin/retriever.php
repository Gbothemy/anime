<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';
?>
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="cm-card p-3 h-100">
      <h1 class="h6 mb-2">Import from MangaDex</h1>
      <form method="post" action="<?php echo e(base_url('manga_retriever.php')); ?>">
        <input type="hidden" name="mode" value="mangadex">
        <div class="mb-2">
          <label class="form-label">Search Query (optional)</label>
          <input class="form-control" name="query" placeholder="e.g., One Piece">
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Language</label>
            <input class="form-control" name="lang" value="<?php echo e(IMPORT_LANGUAGE); ?>">
          </div>
          <div class="col-6">
            <label class="form-label">Limit</label>
            <input type="number" min="1" max="50" class="form-control" name="limit" value="<?php echo (int)IMPORT_LIMIT_PER_RUN; ?>">
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-gradient" type="submit">Run Import</button>
        </div>
      </form>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="cm-card p-3 h-100">
      <h1 class="h6 mb-2">Local ZIP Upload</h1>
      <form method="post" action="<?php echo e(base_url('manga_retriever.php')); ?>" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="zip">
        <div class="mb-2">
          <label class="form-label">Select Manga</label>
          <select class="form-select" name="manga_id" required>
            <option value="">Choose...</option>
            <?php foreach (db_query('SELECT id, title FROM mangas ORDER BY title')->fetchAll() as $m): ?>
              <option value="<?php echo (int)$m['id']; ?>"><?php echo e($m['title']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">ZIP File (images only)</label>
          <input type="file" class="form-control" name="zip" accept="application/zip" required>
        </div>
        <div class="mt-3">
          <button class="btn btn-gradient" type="submit">Upload & Import</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>