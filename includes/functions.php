<?php
require_once __DIR__ . '/config.php';

function base_url(string $path = ''): string {
    $base = BASE_URL;
    if (!$base) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = rtrim($protocol . $host, '/');
    }
    $path = ltrim($path, '/');
    return $path ? $base . '/' . $path : $base;
}

function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slugify(string $text): string {
    $text = trim($text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    return $text ?: uniqid('manga-', true);
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function is_admin(): bool {
    return !empty($_SESSION['user']['is_admin']);
}

function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Please login to continue.'];
        redirect(base_url('login.php'));
    }
}

function require_admin(): void {
    if (!is_admin()) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function ensure_dir(string $path): void {
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function save_uploaded_file(array $file, string $destDir, array $allowedMime = ['image/jpeg','image/png','image/webp','image/svg+xml']): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedMime, true)) {
        return null;
    }
    ensure_dir($destDir);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin';
    $name = uniqid('img_', true) . '.' . strtolower($ext);
    $destPath = rtrim($destDir, '/').'/'.$name;
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return null;
    }
    return $destPath;
}

function paginate(int $total, int $perPage, int $page, string $basePath, array $query = []): string {
    $pages = (int)ceil(max(1, $total) / max(1, $perPage));
    if ($pages <= 1) return '';
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    $queryStr = function(array $q) { return http_build_query($q); };
    for ($i = 1; $i <= $pages; $i++) {
        $active = $i === $page ? ' active' : '';
        $query['page'] = $i;
        $href = e($basePath . '?' . $queryStr($query));
        $html .= '<li class="page-item'.$active.'"><a class="page-link" href="'.$href.'">'.$i.'</a></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}

function seo_url_manga(string $slug): string { return base_url('manga/' . $slug); }
function seo_url_chapter(string $mangaSlug, string $chapterNumber): string { return base_url('chapter/' . $mangaSlug . '/' . rawurlencode($chapterNumber)); }

function send_mail(string $to, string $subject, string $message, string $from = SMTP_FROM): bool {
    $headers = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
    return mail($to, $subject, $message, $headers);
}

function get_genres(): array {
    $stmt = db()->query('SELECT id, name, slug FROM genres ORDER BY name ASC');
    return $stmt->fetchAll();
}

function get_featured_mangas(int $limit = 8): array {
    $sql = 'SELECT m.* FROM mangas m WHERE m.is_featured = 1 ORDER BY m.created_at DESC LIMIT :lim';
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_latest_mangas(int $limit = 12): array {
    $sql = 'SELECT m.* FROM mangas m ORDER BY m.updated_at DESC, m.created_at DESC LIMIT :lim';
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_manga_by_slug(string $slug): ?array {
    $sql = 'SELECT m.* FROM mangas m WHERE m.slug = :slug LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $manga = $stmt->fetch();
    if (!$manga) {
        return null;
    }
    $gStmt = db()->prepare('SELECT g.* FROM genres g JOIN manga_genres mg ON mg.genre_id = g.id WHERE mg.manga_id = :mid ORDER BY g.name');
    $gStmt->execute([':mid' => $manga['id']]);
    $manga['genres'] = $gStmt->fetchAll();
    return $manga;
}

function get_genres_for_manga(int $mangaId): array {
    $gStmt = db()->prepare('SELECT g.name FROM genres g JOIN manga_genres mg ON mg.genre_id = g.id WHERE mg.manga_id = :mid ORDER BY g.name');
    $gStmt->execute([':mid' => $mangaId]);
    return array_map(function($r){ return $r['name']; }, $gStmt->fetchAll());
}

function get_manga_chapters(int $mangaId): array {
    $stmt = db()->prepare('SELECT * FROM chapters WHERE manga_id = :mid ORDER BY chapter_number ASC');
    $stmt->execute([':mid' => $mangaId]);
    return $stmt->fetchAll();
}

function get_chapter_by_id(int $chapterId): ?array {
    $stmt = db()->prepare('SELECT c.*, m.title AS manga_title, m.slug AS manga_slug FROM chapters c JOIN mangas m ON c.manga_id = m.id WHERE c.id = :id');
    $stmt->execute([':id' => $chapterId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_chapter_by_slug_and_number(string $mangaSlug, $chapterNumber): ?array {
    $stmt = db()->prepare('SELECT c.*, m.title AS manga_title, m.slug AS manga_slug FROM chapters c JOIN mangas m ON c.manga_id = m.id WHERE m.slug = :slug AND c.chapter_number = :num');
    $stmt->execute([':slug' => $mangaSlug, ':num' => $chapterNumber]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_prev_next_chapter(int $mangaId, $chapterNumber): array {
    $prevStmt = db()->prepare('SELECT id, chapter_number FROM chapters WHERE manga_id = :mid AND chapter_number < :num ORDER BY chapter_number DESC LIMIT 1');
    $prevStmt->execute([':mid' => $mangaId, ':num' => $chapterNumber]);
    $prev = $prevStmt->fetch();

    $nextStmt = db()->prepare('SELECT id, chapter_number FROM chapters WHERE manga_id = :mid AND chapter_number > :num ORDER BY chapter_number ASC LIMIT 1');
    $nextStmt->execute([':mid' => $mangaId, ':num' => $chapterNumber]);
    $next = $nextStmt->fetch();

    return ['prev' => $prev, 'next' => $next];
}

function get_chapter_images(int $chapterId): array {
    $stmt = db()->prepare('SELECT * FROM chapter_images WHERE chapter_id = :cid ORDER BY page_number ASC');
    $stmt->execute([':cid' => $chapterId]);
    return $stmt->fetchAll();
}

function toggle_bookmark(int $userId, int $mangaId): bool {
    $check = db_query('SELECT 1 FROM bookmarks WHERE user_id = :uid AND manga_id = :mid', [':uid'=>$userId, ':mid'=>$mangaId])->fetch();
    if ($check) {
        db_query('DELETE FROM bookmarks WHERE user_id = :uid AND manga_id = :mid', [':uid'=>$userId, ':mid'=>$mangaId]);
        return false;
    }
    db_query('INSERT INTO bookmarks (user_id, manga_id) VALUES (:uid, :mid)', [':uid'=>$userId, ':mid'=>$mangaId]);
    return true;
}

function is_bookmarked(int $userId, int $mangaId): bool {
    $row = db_query('SELECT 1 FROM bookmarks WHERE user_id = :uid AND manga_id = :mid', [':uid'=>$userId, ':mid'=>$mangaId])->fetch();
    return (bool)$row;
}

function add_reading_history(int $userId, int $mangaId, int $chapterId): void {
    db_query('INSERT INTO reading_history (user_id, manga_id, chapter_id, last_page) VALUES (:uid,:mid,:cid,0) ON DUPLICATE KEY UPDATE chapter_id = VALUES(chapter_id), updated_at = CURRENT_TIMESTAMP', [
        ':uid'=>$userId, ':mid'=>$mangaId, ':cid'=>$chapterId
    ]);
}

function find_user_by_email(string $email): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function create_user(string $name, string $email, string $password, string $role = 'user'): int {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role, created_at) VALUES (:name, :email, :hash, :role, NOW())');
    $stmt->execute([':name' => $name, ':email' => $email, ':hash' => $hash, ':role' => $role]);
    return (int)db()->lastInsertId();
}

function page_param(): int {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    return max(1, $page);
}

function fetch_manga_list(array $filters, int $perPage, int $page): array {
    $where = [];
    $params = [];

    if (!empty($filters['q'])) {
        $where[] = '(m.title LIKE :q OR m.description LIKE :q)';
        $params[':q'] = '%' . $filters['q'] . '%';
    }
    if (!empty($filters['genre'])) {
        $where[] = 'EXISTS (SELECT 1 FROM manga_genres mg WHERE mg.manga_id = m.id AND mg.genre_id = :gid)';
        $params[':gid'] = (int)$filters['genre'];
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $order = 'ORDER BY m.updated_at DESC, m.created_at DESC';
    if (!empty($filters['sort'])) {
        if ($filters['sort'] === 'popular') {
            $order = 'ORDER BY (SELECT COUNT(1) FROM reading_history rh WHERE rh.manga_id = m.id) DESC, m.updated_at DESC';
        } elseif ($filters['sort'] === 'az') {
            $order = 'ORDER BY m.title ASC';
        } elseif ($filters['sort'] === 'latest') {
            $order = 'ORDER BY m.updated_at DESC, m.created_at DESC';
        }
    }

    $countSql = 'SELECT COUNT(1) AS cnt FROM mangas m ' . $whereSql;
    $stmt = db()->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    $pager = paginate($total, $perPage, $page, base_url('manga/'));

    $sql = 'SELECT m.* FROM mangas m ' . $whereSql . ' ' . $order . ' LIMIT :lim OFFSET :off';
    $stmt = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', ($page - 1) * $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    return [$rows, $pager];
}

function create_or_get_genre(string $name): int {
    $slug = slugify($name);
    $stmt = db()->prepare('SELECT id FROM genres WHERE slug = :slug LIMIT 1');
    $stmt->execute([':slug' => $slug]);
    $id = $stmt->fetchColumn();
    if ($id) {
        return (int)$id;
    }
    $stmt = db()->prepare('INSERT INTO genres (name, slug) VALUES (:name, :slug)');
    $stmt->execute([':name' => $name, ':slug' => $slug]);
    return (int)db()->lastInsertId();
}

function db(): PDO { return getPDO(); }
function verify_csrf_token(string $t): bool { return verify_csrf($t); }
?>