<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

$q = trim($_GET['q'] ?? '');
$genreId = (int)($_GET['genre'] ?? 0);
$sort = $_GET['sort'] ?? 'latest';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;

$genres = db_query('SELECT * FROM genres ORDER BY name')->fetchAll();

$where = [];
$params = [];
if ($q !== '') { $where[] = 'm.title LIKE :q'; $params[':q'] = '%' . $q . '%'; }
if ($genreId > 0) { $where[] = 'EXISTS (SELECT 1 FROM manga_genres mg WHERE mg.manga_id=m.id AND mg.genre_id=:gid)'; $params[':gid'] = $genreId; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$order = 'ORDER BY m.updated_at DESC';
if ($sort === 'popular') { $order = 'ORDER BY m.popularity_score DESC, m.updated_at DESC'; }
if ($sort === 'az') { $order = 'ORDER BY m.title ASC'; }

$total = (int)db_query("SELECT COUNT(*) FROM mangas m $whereSql", $params)->fetchColumn();
$offset = ($page - 1) * $perPage;
$rows = db_query("SELECT m.*, a.name AS author_name FROM mangas m LEFT JOIN authors a ON a.id=m.author_id $whereSql $order LIMIT :lim OFFSET :off", array_merge($params, [':lim'=>$perPage, ':off'=>$offset]))->fetchAll();

function render_sort_link($label, $value) {
  $query = $_GET;
  $query['sort'] = $value;
  $href = e('?' . http_build_query($query));
  $active = ($value === ($_GET['sort'] ?? 'latest')) ? 'active' : '';
  echo '<a class="btn btn-sm btn-outline-info '.$active.'" href="'.$href.'">'.$label.'</a>';
}
?>
<h1 class="h4 mb-3">Explore Mangas</h1>
<div class="cm-card p-3 mb-3">
  <form class="row g-2" method="get">
    <div class="col-12 col-md-4">
      <input type="text" class="form-control" name="q" placeholder="Search title..." value="<?php echo e($q); ?>">
    </div>
    <div class="col-6 col-md-3">
      <select class="form-select" name="genre">
        <option value="0">All Genres</option>
        <?php foreach ($genres as $g): ?>
          <option value="<?php echo (int)$g['id']; ?>" <?php echo $genreId===(int)$g['id']?'selected':''; ?>><?php echo e($g['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-5 d-flex align-items-center gap-2">
      <?php render_sort_link('Latest','latest'); ?>
      <?php render_sort_link('Most Popular','popular'); ?>
      <?php render_sort_link('A-Z','az'); ?>
      <button class="btn btn-gradient btn-sm ms-auto" type="submit">Apply</button>
    </div>
  </form>
</div>

<div class="row g-3">
  <?php foreach ($rows as $m): ?>
    <div class="col-6 col-md-3 col-lg-2">
      <div class="cm-card h-100">
        <a href="<?php echo e(seo_url_manga($m['slug'])); ?>" class="text-decoration-none">
          <img class="manga-cover" src="<?php echo e(base_url($m['cover_image'] ?: 'uploads/mangas/placeholder.svg')); ?>" alt="<?php echo e($m['title']); ?>">
          <div class="p-2">
            <div class="fw-semibold small text-truncate" title="<?php echo e($m['title']); ?>"><?php echo e($m['title']); ?></div>
            <div class="text-muted small text-truncate"><?php echo e($m['author_name'] ?: 'Unknown'); ?></div>
          </div>
        </a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$rows): ?>
    <div class="col-12"><div class="alert alert-warning">No results found.</div></div>
  <?php endif; ?>
</div>
<?php echo paginate($total, $perPage, $page, base_url('mangas'), $_GET); ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>