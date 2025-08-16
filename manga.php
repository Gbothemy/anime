<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$manga = db_query('SELECT m.*, a.name AS author_name FROM mangas m LEFT JOIN authors a ON a.id=m.author_id WHERE m.slug=:s', [':s'=>$slug])->fetch();
if (!$manga) { http_response_code(404); echo '<div class="alert alert-danger">Manga not found.</div>'; require_once __DIR__ . '/includes/footer.php'; exit; }

$genres = db_query('SELECT g.* FROM genres g JOIN manga_genres mg ON mg.genre_id=g.id WHERE mg.manga_id=:id ORDER BY g.name', [':id'=>$manga['id']])->fetchAll();
$chapters = db_query('SELECT * FROM chapters WHERE manga_id=:id ORDER BY CAST(chapter_number AS DECIMAL(10,3)) DESC, id DESC', [':id'=>$manga['id']])->fetchAll();
?>
<div class="row g-3">
  <div class="col-12 col-md-3">
    <div class="cm-card p-2">
      <img class="w-100" style="border-radius: 10px;" src="<?php echo e(base_url($manga['cover_image'] ?: 'uploads/mangas/placeholder.svg')); ?>" alt="<?php echo e($manga['title']); ?>">
    </div>
  </div>
  <div class="col-12 col-md-9">
    <div class="cm-card p-3">
      <h1 class="h4 mb-2"><?php echo e($manga['title']); ?></h1>
      <div class="text-muted mb-2">By <?php echo e($manga['author_name'] ?: 'Unknown'); ?> <?php if ($manga['release_date']): ?> • Released <?php echo e(date('Y', strtotime($manga['release_date']))); ?><?php endif; ?></div>
      <div class="mb-2">
        <?php foreach ($genres as $g): ?><span class="cm-tag me-1 mb-1 d-inline-block"><?php echo e($g['name']); ?></span><?php endforeach; ?>
      </div>
      <p class="mb-3"><?php echo nl2br(e($manga['description'] ?? '')); ?></p>
      <?php if (is_logged_in()): ?>
        <form method="post" action="<?php echo e(base_url('toggle_bookmark.php')); ?>">
          <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
          <input type="hidden" name="manga_id" value="<?php echo (int)$manga['id']; ?>">
          <button class="btn btn-outline-info btn-sm" type="submit"><i class="bi bi-bookmark"></i> Bookmark</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="cm-card p-3 mt-3">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h2 class="h5 mb-0">Chapters</h2>
    <div class="text-muted small">Newest first</div>
  </div>
  <div class="list-group list-group-flush">
    <?php foreach ($chapters as $c): ?>
      <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?php echo e(seo_url_chapter($manga['slug'], $c['chapter_number'])); ?>">
        <span>Chapter <?php echo e($c['chapter_number']); ?><?php if ($c['title']): ?>: <?php echo e($c['title']); ?><?php endif; ?></span>
        <i class="bi bi-chevron-right"></i>
      </a>
    <?php endforeach; ?>
    <?php if (!$chapters): ?>
      <div class="list-group-item">No chapters yet.</div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>