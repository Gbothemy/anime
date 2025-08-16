<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Manga List';
$genres = get_genres();

$filters = [
    'q' => $_GET['q'] ?? '',
    'genre' => isset($_GET['genre']) ? (int)$_GET['genre'] : null,
    'sort' => $_GET['sort'] ?? 'latest',
];
list($rows, $pager) = fetch_manga_list($filters, ITEMS_PER_PAGE, page_param());
include __DIR__ . '/includes/header.php';
?>
<h3 class="mb-3">Browse</h3>
<form class="row g-2 mb-3" method="get">
  <div class="col-12 col-md-6">
    <input type="text" class="form-control" name="q" placeholder="Search title or description" value="<?php echo htmlspecialchars($filters['q']); ?>">
  </div>
  <div class="col-6 col-md-3">
    <select class="form-select" name="genre">
      <option value="">All Genres</option>
      <?php foreach ($genres as $g): ?>
        <option value="<?php echo $g['id']; ?>" <?php echo ($filters['genre'] == $g['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($g['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2">
    <select class="form-select" name="sort">
      <option value="latest" <?php echo $filters['sort']==='latest'?'selected':''; ?>>Latest</option>
      <option value="popular" <?php echo $filters['sort']==='popular'?'selected':''; ?>>Most Popular</option>
      <option value="az" <?php echo $filters['sort']==='az'?'selected':''; ?>>A-Z</option>
    </select>
  </div>
  <div class="col-12 col-md-1 d-grid">
    <button class="btn btn-primary" type="submit">Filter</button>
  </div>
</form>

<div class="row g-3">
<?php foreach ($rows as $m): ?>
  <div class="col-6 col-md-4 col-lg-3">
    <div class="card manga-card h-100">
      <img src="<?php echo UPLOADS_URL . '/' . htmlspecialchars($m['cover_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($m['title']); ?>">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-1" style="min-height:2.5rem;">
          <a class="stretched-link text-decoration-none" href="<?php echo url_manga($m['slug']); ?>"><?php echo htmlspecialchars($m['title']); ?></a>
        </h5>
        <div class="manga-meta">Released: <?php echo htmlspecialchars($m['release_date']); ?></div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php if ($pager['pages'] > 1): ?>
<nav aria-label="Page navigation" class="my-4">
  <ul class="pagination justify-content-center">
    <?php for ($p = 1; $p <= $pager['pages']; $p++): ?>
      <li class="page-item <?php echo $p === $pager['page'] ? 'active' : ''; ?>">
        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo $p; ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>