<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/db.php';

function md_get(string $endpoint, array $params = []): array {
    $url = MANGADEX_BASE_API . $endpoint . (empty($params) ? '' : ('?' . http_build_query($params)));
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_USERAGENT=>'CrypyedMangaBot/1.0']);
    $resp = curl_exec($ch);
    if ($resp === false) { return []; }
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($code >= 400) { return []; }
    $data = json_decode($resp, true);
    return is_array($data) ? $data : [];
}

function md_download(string $url, string $destPath): bool {
    ensure_dir(dirname($destPath));
    $fp = fopen($destPath, 'w');
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_FILE=>$fp, CURLOPT_FOLLOWLOCATION=>true, CURLOPT_USERAGENT=>'CrypyedMangaBot/1.0']);
    $ok = curl_exec($ch) !== false && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) < 400;
    curl_close($ch); fclose($fp);
    return $ok;
}

function import_from_mangadex(string $query = '', string $lang = IMPORT_LANGUAGE, int $limit = IMPORT_LIMIT_PER_RUN): array {
    $params = [
        'limit' => $limit,
        'availableTranslatedLanguage[]' => $lang,
        'includes[]' => ['author','artist','cover_art'],
        'order[latestUploadedChapter]' => 'desc',
    ];
    if ($query) { $params['title'] = $query; }
    $res = md_get('/manga', $params);
    $imported = [];
    foreach (($res['data'] ?? []) as $m) {
        $mdId = $m['id'] ?? null;
        $attrs = $m['attributes'] ?? [];
        $title = $attrs['title'][$lang] ?? (is_array($attrs['title']) ? (array_values($attrs['title'])[0] ?? 'Untitled') : 'Untitled');
        $desc = $attrs['description'][$lang] ?? (is_array($attrs['description'] ?? null) ? (array_values($attrs['description'])[0] ?? '') : ($attrs['description'] ?? ''));
        $slug = slugify($title);

        $authorName = null;
        $coverFileName = null;
        $coverUrl = null;
        foreach ($m['relationships'] ?? [] as $rel) {
            if (($rel['type'] ?? '') === 'author') { $authorName = $rel['attributes']['name'] ?? $authorName; }
            if (($rel['type'] ?? '') === 'cover_art') { $coverFileName = $rel['attributes']['fileName'] ?? $coverFileName; }
        }
        if ($coverFileName) {
            $coverUrl = MANGADEX_UPLOADS_BASE . '/covers/' . $mdId . '/' . $coverFileName;
        }

        $authorId = null;
        if ($authorName) {
            $author = db_query('SELECT id FROM authors WHERE name=:n', [':n'=>$authorName])->fetch();
            if (!$author) { db_query('INSERT INTO authors (name) VALUES (:n)', [':n'=>$authorName]); $authorId = (int)db_last_insert_id(); }
            else { $authorId = (int)$author['id']; }
        }

        $existing = db_query('SELECT id FROM mangas WHERE mangadex_id=:md OR slug=:s', [':md'=>$mdId, ':s'=>$slug])->fetch();
        if ($existing) {
            $mangaId = (int)$existing['id'];
            db_query('UPDATE mangas SET title=:t, description=:d, author_id=:a, updated_at=NOW() WHERE id=:id', [':t'=>$title, ':d'=>$desc, ':a'=>$authorId, ':id'=>$mangaId]);
        } else {
            $coverPath = 'uploads/mangas/placeholder.svg';
            if ($coverUrl) {
                $local = __DIR__ . '/uploads/mangas/' . $mdId . '_' . basename($coverFileName);
                if (md_download($coverUrl, $local)) { $coverPath = 'uploads/mangas/' . basename($local); }
            }
            db_query('INSERT INTO mangas (mangadex_id, slug, title, description, author_id, cover_image, language) VALUES (:md,:s,:t,:d,:a,:c,:lang)', [
                ':md'=>$mdId, ':s'=>$slug, ':t'=>$title, ':d'=>$desc, ':a'=>$authorId, ':c'=>$coverPath, ':lang'=>$lang
            ]);
            $mangaId = (int)db_last_insert_id();
        }

        // Map genres from tags
        $tags = $attrs['tags'] ?? [];
        foreach ($tags as $tag) {
            $tAttr = $tag['attributes'] ?? [];
            $name = $tAttr['name'][$lang] ?? (is_array($tAttr['name'] ?? null) ? (array_values($tAttr['name'])[0] ?? null) : null);
            if (!$name) continue;
            db_query('INSERT IGNORE INTO genres (name) VALUES (:n)', [':n'=>$name]);
            $gid = db_query('SELECT id FROM genres WHERE name=:n', [':n'=>$name])->fetchColumn();
            if ($gid) { db_query('INSERT IGNORE INTO manga_genres (manga_id, genre_id) VALUES (:m,:g)', [':m'=>$mangaId, ':g'=>(int)$gid]); }
        }

        // Chapters
        $chapRes = md_get('/chapter', [
            'manga' => $mdId,
            'translatedLanguage[]' => $lang,
            'order[chapter]' => 'asc',
            'limit' => 100
        ]);
        foreach (($chapRes['data'] ?? []) as $ch) {
            $chId = $ch['id'] ?? null;
            $cat = $ch['attributes'] ?? [];
            $number = (string)($cat['chapter'] ?? '') ?: ($cat['title'] ?? '0');
            $ctitle = $cat['title'] ?? '';
            if (!$chId || !$number) continue;

            $exists = db_query('SELECT id FROM chapters WHERE mangadex_chapter_id=:cid OR (manga_id=:m AND chapter_number=:n)', [':cid'=>$chId, ':m'=>$mangaId, ':n'=>$number])->fetch();
            if ($exists) { continue; }

            db_query('INSERT INTO chapters (manga_id, mangadex_chapter_id, chapter_number, title, upload_source) VALUES (:m,:cid,:n,:t,\'mangadex\')', [
                ':m'=>$mangaId, ':cid'=>$chId, ':n'=>$number, ':t'=>$ctitle
            ]);
            $chapterId = (int)db_last_insert_id();

            // At-home server to get image base URL
            $atHome = md_get('/at-home/server/' . $chId);
            $base = ($atHome['baseUrl'] ?? '') . '/data/';
            $hash = $atHome['chapter']['hash'] ?? '';
            $pages = $atHome['chapter']['data'] ?? [];
            $pageNo = 1;
            foreach ($pages as $file) {
                $imgUrl = $base . $hash . '/' . $file;
                $local = __DIR__ . '/uploads/mangas/' . $chId . '_' . $file;
                if (md_download($imgUrl, $local)) {
                    db_query('INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES (:c,:p,:path)', [
                        ':c'=>$chapterId, ':p'=>$pageNo, ':path' => 'uploads/mangas/'.basename($local)
                    ]);
                    $pageNo++;
                }
            }
            db_query('UPDATE chapters SET pages_count=:pc WHERE id=:id', [':pc'=>$pageNo-1, ':id'=>$chapterId]);
        }

        $imported[] = $title;
    }
    return $imported;
}

function import_from_zip(int $mangaId, string $zipTmpPath): bool {
    $zip = new ZipArchive();
    if ($zip->open($zipTmpPath) === TRUE) {
        $extractDir = __DIR__ . '/uploads/mangas/tmp_' . uniqid();
        ensure_dir($extractDir);
        $zip->extractTo($extractDir);
        $zip->close();
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractDir));
        $images = [];
        foreach ($files as $file) {
            if ($file->isDir()) continue;
            $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) { $images[] = $file->getPathname(); }
        }
        sort($images, SORT_NATURAL);
        db_query('INSERT INTO chapters (manga_id, chapter_number, title, upload_source) VALUES (:m,:n,:t,\'local\')', [':m'=>$mangaId, ':n'=>date('YmdHis'), ':t'=>'Uploaded']);
        $chapterId = (int)db_last_insert_id();
        $page = 1;
        foreach ($images as $img) {
            $dest = __DIR__ . '/uploads/mangas/' . $mangaId . '_' . basename($img);
            copy($img, $dest);
            db_query('INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES (:c,:p,:path)', [
                ':c'=>$chapterId, ':p'=>$page++, ':path' => 'uploads/mangas/' . basename($dest)
            ]);
        }
        db_query('UPDATE chapters SET pages_count=:pc WHERE id=:id', [':pc'=>$page-1, ':id'=>$chapterId]);
        // Cleanup
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $f) { $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath()); }
        rmdir($extractDir);
        return true;
    }
    return false;
}

if (php_sapi_name() === 'cli') {
    $query = $argv[1] ?? '';
    $list = import_from_mangadex($query);
    echo 'Imported/Updated: ' . implode(', ', $list) . PHP_EOL;
} else {
    if (!is_admin()) { http_response_code(403); echo 'Forbidden'; exit; }
    $mode = $_POST['mode'] ?? 'mangadex';
    if ($mode === 'mangadex') {
        $query = trim($_POST['query'] ?? '');
        $lang = $_POST['lang'] ?? IMPORT_LANGUAGE;
        $limit = max(1, min(50, (int)($_POST['limit'] ?? IMPORT_LIMIT_PER_RUN)));
        $list = import_from_mangadex($query, $lang, $limit);
        $_SESSION['flash']=['type'=>'success','msg'=>'Imported/updated: ' . e(implode(', ', $list))];
        redirect(base_url('admin/retriever.php'));
    } elseif ($mode === 'zip') {
        $mangaId = (int)($_POST['manga_id'] ?? 0);
        if ($mangaId <= 0) { $_SESSION['flash']=['type'=>'danger','msg'=>'Invalid manga ID']; redirect(base_url('admin/retriever.php')); }
        if (!empty($_FILES['zip']['tmp_name'])) {
            $ok = import_from_zip($mangaId, $_FILES['zip']['tmp_name']);
            $_SESSION['flash']=['type'=>$ok?'success':'danger','msg'=>$ok?'ZIP imported':'Failed to import ZIP'];
        }
        redirect(base_url('admin/retriever.php'));
    } else {
        http_response_code(400); echo 'Unknown mode';
    }
}