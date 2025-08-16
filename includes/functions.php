<?php
require_once __DIR__ . '/auth.php';

function slugify(string $text): string {
    $text = preg_replace('~[\p{Pd}\s]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

function send_mail(string $to, string $subject, string $message, string $from = MAIL_FROM): bool {
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
    $check = db()->prepare('SELECT 1 FROM bookmarks WHERE user_id = :uid AND manga_id = :mid');
    $check->execute([':uid' => $userId, ':mid' => $mangaId]);
    if ($check->fetch()) {
        $del = db()->prepare('DELETE FROM bookmarks WHERE user_id = :uid AND manga_id = :mid');
        $del->execute([':uid' => $userId, ':mid' => $mangaId]);
        return false; // removed
    }
    $ins = db()->prepare('INSERT INTO bookmarks (user_id, manga_id, created_at) VALUES (:uid, :mid, NOW())');
    $ins->execute([':uid' => $userId, ':mid' => $mangaId]);
    return true; // added
}

function is_bookmarked(int $userId, int $mangaId): bool {
    $stmt = db()->prepare('SELECT 1 FROM bookmarks WHERE user_id = :uid AND manga_id = :mid');
    $stmt->execute([':uid' => $userId, ':mid' => $mangaId]);
    return (bool)$stmt->fetch();
}

function add_reading_history(int $userId, int $mangaId, int $chapterId): void {
    $stmt = db()->prepare('INSERT INTO reading_history (user_id, manga_id, chapter_id, last_read_at) VALUES (:uid, :mid, :cid, NOW()) ON DUPLICATE KEY UPDATE chapter_id = VALUES(chapter_id), last_read_at = VALUES(last_read_at)');
    $stmt->execute([':uid' => $userId, ':mid' => $mangaId, ':cid' => $chapterId]);
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

function paginate(int $totalItems, int $perPage, int $currentPage): array {
    $totalPages = (int)ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages ?: 1));
    $offset = ($currentPage - 1) * $perPage;
    return [
        'total' => $totalItems,
        'pages' => $totalPages,
        'page' => $currentPage,
        'perPage' => $perPage,
        'offset' => $offset,
    ];
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

    $pager = paginate($total, $perPage, $page);

    $sql = 'SELECT m.* FROM mangas m ' . $whereSql . ' ' . $order . ' LIMIT :lim OFFSET :off';
    $stmt = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':lim', $pager['perPage'], PDO::PARAM_INT);
    $stmt->bindValue(':off', $pager['offset'], PDO::PARAM_INT);
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
?>