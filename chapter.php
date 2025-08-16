<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$chapterNum = $_GET['num'] ?? '';
$manga = db_query('SELECT * FROM mangas WHERE slug=:s', [':s'=>$slug])->fetch();
if (!$manga) { http_response_code(404); echo '<div class="alert alert-danger">Manga not found.</div>'; require_once __DIR__ . '/includes/footer.php'; exit; }

$chapter = db_query('SELECT * FROM chapters WHERE manga_id=:mid AND chapter_number=:num', [':mid'=>$manga['id'], ':num'=>$chapterNum])->fetch();
if (!$chapter) { http_response_code(404); echo '<div class="alert alert-danger">Chapter not found.</div>'; require_once __DIR__ . '/includes/footer.php'; exit; }
$images = db_query('SELECT * FROM chapter_images WHERE chapter_id=:cid ORDER BY page_number ASC', [':cid'=>$chapter['id']])->fetchAll();

if (is_logged_in()) { add_reading_history((int)current_user()['id'], (int)$manga['id'], (int)$chapter['id']); }

$prev = db_query('SELECT chapter_number FROM chapters WHERE manga_id=:mid AND CAST(chapter_number AS DECIMAL(10,3)) < CAST(:num AS DECIMAL(10,3)) ORDER BY CAST(chapter_number AS DECIMAL(10,3)) DESC LIMIT 1', [':mid'=>$manga['id'], ':num'=>$chapterNum])->fetchColumn();
$next = db_query('SELECT chapter_number FROM chapters WHERE manga_id=:mid AND CAST(chapter_number AS DECIMAL(10,3)) > CAST(:num AS DECIMAL(10,3)) ORDER BY CAST(chapter_number AS DECIMAL(10,3)) ASC LIMIT 1', [':mid'=>$manga['id'], ':num'=>$chapterNum])->fetchColumn();
?>
<div class="reader-container">
  <div class="reader-toolbar d-flex align-items-center justify-content-between p-2 mb-3">
    <div class="d-flex align-items-center gap-2">
      <a class="btn btn-sm btn-outline-info" href="<?php echo e(seo_url_manga($manga['slug'])); ?>"><i class="bi bi-arrow-left"></i> <?php echo e($manga['title']); ?></a>
      <span class="small text-muted">Chapter <?php echo e($chapter['chapter_number']); ?><?php if ($chapter['title']): ?> • <?php echo e($chapter['title']); ?><?php endif; ?></span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <?php if ($prev): ?><a class="btn btn-sm btn-outline-info" href="<?php echo e(seo_url_chapter($manga['slug'], $prev)); ?>">Prev</a><?php endif; ?>
      <?php if ($next): ?><a class="btn btn-sm btn-outline-info" href="<?php echo e(seo_url_chapter($manga['slug'], $next)); ?>">Next</a><?php endif; ?>
      <button class="btn btn-sm btn-gradient" type="button" data-reader-toggle><i class="bi bi-brightness-high"></i> Mode</button>
    </div>
  </div>
  <?php foreach ($images as $img): ?>
    <img class="reader-image" src="<?php echo e(base_url($img['image_path'])); ?>" alt="Page <?php echo (int)$img['page_number']; ?>">
  <?php endforeach; ?>
  <div class="d-flex align-items-center justify-content-between my-3">
    <?php if ($prev): ?><a class="btn btn-outline-info" href="<?php echo e(seo_url_chapter($manga['slug'], $prev)); ?>">Prev Chapter</a><?php else: ?><span></span><?php endif; ?>
    <?php if ($next): ?><a class="btn btn-outline-info" href="<?php echo e(seo_url_chapter($manga['slug'], $next)); ?>">Next Chapter</a><?php endif; ?>
  </div>
  <div class="text-center mb-4"><button class="btn btn-sm btn-outline-info" data-scroll-top>Back to Top</button></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>