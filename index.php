<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

$featured = db_query("SELECT * FROM mangas ORDER BY popularity_score DESC, updated_at DESC LIMIT 5")->fetchAll();
$latest = db_query("SELECT m.*, a.name AS author_name,
  (SELECT GROUP_CONCAT(g.name SEPARATOR ', ') FROM manga_genres mg JOIN genres g ON g.id=mg.genre_id WHERE mg.manga_id=m.id) AS genre_names
  FROM mangas m LEFT JOIN authors a ON a.id=m.author_id ORDER BY updated_at DESC LIMIT 12")->fetchAll();
?>
<div class="cm-hero p-3 p-md-4 mb-4">
  <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <?php foreach ($featured as $i => $m): ?>
        <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
          <img src="<?php echo e(base_url($m['cover_image'] ?: 'uploads/mangas/placeholder.svg')); ?>" class="d-block w-100" alt="<?php echo e($m['title']); ?>">
          <div class="carousel-caption d-none d-md-block text-start">
            <h5 class="fw-bold"><?php echo e($m['title']); ?></h5>
            <p class="text-muted"><?php echo e(mb_strimwidth(strip_tags($m['description'] ?? ''), 0, 140, '…')); ?></p>
            <a class="btn btn-gradient btn-sm glow" href="<?php echo e(seo_url_manga($m['slug'])); ?>">Read Now</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
  </div>
</div>

<h2 class="h5 mb-3">Latest Updates</h2>
<div class="row g-3">
  <?php foreach ($latest as $m): ?>
    <div class="col-6 col-md-3 col-lg-2">
      <div class="cm-card h-100 position-relative">
        <a href="<?php echo e(seo_url_manga($m['slug'])); ?>" class="text-decoration-none">
          <img class="manga-cover" src="<?php echo e(base_url($m['cover_image'] ?: 'uploads/mangas/placeholder.svg')); ?>" alt="<?php echo e($m['title']); ?>">
          <div class="p-2">
            <div class="fw-semibold small text-truncate" title="<?php echo e($m['title']); ?>"><?php echo e($m['title']); ?></div>
            <div class="text-muted small text-truncate"><?php echo e($m['genre_names'] ?: '—'); ?></div>
            <div class="text-muted small"><?php echo e(mb_strimwidth(strip_tags($m['description'] ?? ''), 0, 60, '…')); ?></div>
          </div>
        </a>
        <?php if (is_logged_in()): ?>
        <form method="post" action="<?php echo e(base_url('toggle_bookmark.php')); ?>" class="position-absolute" style="right:6px; top:6px;">
          <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
          <input type="hidden" name="manga_id" value="<?php echo (int)$m['id']; ?>">
          <button class="btn btn-sm btn-outline-info" title="Bookmark"><i class="bi bi-bookmark"></i></button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>