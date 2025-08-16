<?php
require_once __DIR__ . '/../includes/header.php';
require_admin();
?>
<div class="cm-card p-3">
  <h1 class="h5 mb-3">Admin Dashboard</h1>
  <div class="row g-3">
    <div class="col-12 col-md-6 col-lg-4">
      <a class="cm-card p-3 d-block text-decoration-none" href="mangas.php">
        <div class="fw-semibold">Manage Mangas</div>
        <div class="text-muted small">Add, edit, delete mangas</div>
      </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
      <a class="cm-card p-3 d-block text-decoration-none" href="chapters.php">
        <div class="fw-semibold">Manage Chapters</div>
        <div class="text-muted small">Add images, edit chapters</div>
      </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
      <a class="cm-card p-3 d-block text-decoration-none" href="genres.php">
        <div class="fw-semibold">Genres & Authors</div>
        <div class="text-muted small">Manage categories and authors</div>
      </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
      <a class="cm-card p-3 d-block text-decoration-none" href="messages.php">
        <div class="fw-semibold">Messages</div>
        <div class="text-muted small">View and reply to contact messages</div>
      </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
      <a class="cm-card p-3 d-block text-decoration-none" href="retriever.php">
        <div class="fw-semibold">Manga Retriever</div>
        <div class="text-muted small">Import from MangaDex or upload ZIP</div>
      </a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>