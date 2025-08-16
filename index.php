<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home';
$featured = get_featured_mangas(6);
$latest = get_latest_mangas(12);
include __DIR__ . '/includes/header.php';
?>
<?php if ($featured): ?>
<div class="row">
  <div class="col-12">
    <div id="featuredCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php foreach ($featured as $idx => $m): ?>
          <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
            <img src="<?php echo UPLOADS_URL . '/' . htmlspecialchars($m['cover_image']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($m['title']); ?>" style="height:380px; object-fit:cover;">
            <div class="carousel-caption d-none d-md-block">
              <h5><?php echo htmlspecialchars($m['title']); ?></h5>
              <p><?php echo htmlspecialchars(mb_strimwidth($m['description'], 0, 120, '…')); ?></p>
              <a href="<?php echo url_manga($m['slug']); ?>" class="btn btn-primary">Read</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<h3 class="mb-3">Latest Updates</h3>
<div class="row g-3">
  <?php foreach ($latest as $m): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card manga-card h-100">
        <img src="<?php echo UPLOADS_URL . '/' . htmlspecialchars($m['cover_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($m['title']); ?>">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title mb-1" style="min-height:2.5rem;">
            <a class="stretched-link text-decoration-none" href="<?php echo url_manga($m['slug']); ?>"><?php echo htmlspecialchars($m['title']); ?></a>
          </h5>
          <div class="manga-meta mb-2">Updated: <?php echo htmlspecialchars(date('Y-m-d', strtotime($m['updated_at'] ?? $m['created_at']))); ?></div>
          <p class="card-text"><?php echo htmlspecialchars(mb_strimwidth($m['description'], 0, 90, '…')); ?></p>
          <div class="manga-meta">Genres: <?php echo htmlspecialchars(implode(', ', get_genres_for_manga((int)$m['id']))); ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>