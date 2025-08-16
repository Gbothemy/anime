#!/usr/bin/env php
<?php
require_once __DIR__ . '/../includes/functions.php';

function cli_error($msg) { fwrite(STDERR, $msg . "\n"); exit(1); }

$args = $argv;
array_shift($args);
$mode = null; $id = null; $lang = 'en'; $zip = null;
for ($i=0; $i<count($args); $i++) {
    if ($args[$i] === '--mangadex') { $mode = 'mangadex'; $id = $args[++$i] ?? null; }
    elseif ($args[$i] === '--lang') { $lang = $args[++$i] ?? 'en'; }
    elseif ($args[$i] === '--upload') { $mode = 'upload'; $zip = $args[++$i] ?? null; }
}

if ($mode === 'mangadex') {
    if (!$id) cli_error('Missing MangaDex ID');
    $base = 'https://api.mangadex.org';
    $mangaUrl = $base . '/manga/' . urlencode($id) . '?includes[]=author&includes[]=artist&includes[]=cover_art';
    $json = file_get_contents($mangaUrl);
    if ($json === false) cli_error('Failed to fetch manga');
    $data = json_decode($json, true);
    if (!$data || empty($data['data'])) cli_error('Invalid response');
    $m = $data['data'];
    $attributes = $m['attributes'];

    $title = $attributes['title']['en'] ?? reset($attributes['title']) ?? 'Untitled';
    $desc = $attributes['description']['en'] ?? '';
    $authorName = 'Unknown';
    foreach ($m['relationships'] as $rel) {
        if ($rel['type'] === 'author' && isset($rel['attributes']['name'])) { $authorName = $rel['attributes']['name']; break; }
    }

    $slug = slugify($title);
    $coverFileName = '';
    foreach ($m['relationships'] as $rel) {
        if ($rel['type'] === 'cover_art') {
            $coverFileName = $rel['attributes']['fileName'] ?? '';
        }
    }
    $coverPath = '';
    if ($coverFileName) {
        $coverUrl = 'https://uploads.mangadex.org/covers/' . $id . '/' . $coverFileName;
        $coverBin = @file_get_contents($coverUrl);
        if ($coverBin !== false) {
            $ext = pathinfo($coverFileName, PATHINFO_EXTENSION) ?: 'jpg';
            $local = 'cover_' . $slug . '_' . time() . '.' . strtolower($ext);
            file_put_contents(UPLOADS_PATH . '/' . $local, $coverBin);
            $coverPath = $local;
        }
    }

    $stmt = db()->prepare('INSERT INTO mangas (title, slug, author, description, cover_image, release_date, is_featured, created_at, updated_at) VALUES (:t,:s,:a,:d,:c,NULL,0,NOW(),NOW()) ON DUPLICATE KEY UPDATE author=VALUES(author), description=VALUES(description), cover_image=IF(VALUES(cover_image)="",cover_image,VALUES(cover_image)), updated_at=NOW()');
    $stmt->execute([':t'=>$title, ':s'=>$slug, ':a'=>$authorName, ':d'=>$desc, ':c'=>$coverPath]);
    $mangaIdStmt = db()->prepare('SELECT id FROM mangas WHERE slug=:s');
    $mangaIdStmt->execute([':s'=>$slug]);
    $mangaId = (int)$mangaIdStmt->fetchColumn();

    // Genres
    if (!empty($attributes['tags'])) {
        foreach ($attributes['tags'] as $tag) {
            $gname = $tag['attributes']['name']['en'] ?? null;
            if (!$gname) continue;
            $gid = create_or_get_genre($gname);
            db()->prepare('INSERT IGNORE INTO manga_genres (manga_id, genre_id) VALUES (:m,:g)')->execute([':m'=>$mangaId, ':g'=>$gid]);
        }
    }

    // Fetch chapters (basic)
    $chaptersUrl = $base . '/chapter?manga=' . urlencode($id) . '&translatedLanguage[]=' . urlencode($lang) . '&order[chapter]=asc&limit=50';
    $json = file_get_contents($chaptersUrl);
    $chap = json_decode($json, true);
    if ($chap && !empty($chap['data'])) {
        foreach ($chap['data'] as $c) {
            $cAttr = $c['attributes'];
            $num = $cAttr['chapter'] ?: $c['id'];
            $titleC = $cAttr['title'] ?? '';
            $stmt = db()->prepare('INSERT INTO chapters (manga_id, chapter_number, title, created_at) VALUES (:m,:n,:t,NOW()) ON DUPLICATE KEY UPDATE title=VALUES(title)');
            $stmt->execute([':m'=>$mangaId, ':n'=>$num, ':t'=>$titleC]);
            $cid = (int)db()->lastInsertId();
            if ($cid === 0) {
                $cidStmt = db()->prepare('SELECT id FROM chapters WHERE manga_id=:m AND chapter_number=:n');
                $cidStmt->execute([':m'=>$mangaId, ':n'=>$num]);
                $cid = (int)$cidStmt->fetchColumn();
            }
            // Download at least the first page via at-home API
            $atHome = json_decode(@file_get_contents($base . '/at-home/server/' . $c['id']), true);
            if ($atHome && !empty($atHome['baseUrl'])) {
                $hash = $cAttr['hash'] ?? ($c['attributes']['hash'] ?? null);
                $hash = $hash ?: ($atHome['chapter']['hash'] ?? null);
                $pages = $atHome['chapter']['data'] ?? [];
                $pnum = 1;
                foreach ($pages as $p) {
                    $imgUrl = rtrim($atHome['baseUrl'],'/') . '/data/' . $hash . '/' . $p;
                    $bin = @file_get_contents($imgUrl);
                    if ($bin === false) continue;
                    $ext = pathinfo($p, PATHINFO_EXTENSION) ?: 'jpg';
                    $local = 'mdx_' . $cid . '_' . $pnum . '_' . bin2hex(random_bytes(2)) . '.' . strtolower($ext);
                    file_put_contents(UPLOADS_PATH . '/' . $local, $bin);
                    db()->prepare('INSERT IGNORE INTO chapter_images (chapter_id, page_number, image_path) VALUES (:c,:p,:i)')->execute([':c'=>$cid, ':p'=>$pnum, ':i'=>$local]);
                    $pnum++;
                }
            }
        }
    }
    db()->prepare('UPDATE mangas SET updated_at = NOW() WHERE id=:id')->execute([':id'=>$mangaId]);
    echo "Imported: $title\n";
    exit(0);
} elseif ($mode === 'upload') {
    if (!$zip || !file_exists($zip)) cli_error('Missing ZIP path');
    $zipObj = new ZipArchive();
    if ($zipObj->open($zip) !== true) cli_error('Failed to open ZIP');
    // Expect structure: manga_title/chap_<number>/ images
    $extractDir = sys_get_temp_dir() . '/upload_' . bin2hex(random_bytes(3));
    mkdir($extractDir, 0777, true);
    $zipObj->extractTo($extractDir);
    $zipObj->close();

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractDir, FilesystemIterator::SKIP_DOTS));
    $byChapter = [];
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $path = $file->getPathname();
        $rel = str_replace($extractDir . '/', '', $path);
        $parts = explode('/', $rel);
        if (count($parts) < 2) continue;
        $mangaTitle = $parts[0];
        $chapterPart = $parts[1];
        if (!preg_match('~(chap|chapter)[ _-]*(\d+[\.]?\d*)~i', $chapterPart, $matches)) continue;
        $num = $matches[2];
        $byChapter[$mangaTitle][$num][] = $path;
    }

    foreach ($byChapter as $mangaTitle => $chapters) {
        $slug = slugify($mangaTitle);
        $cover = '';
        $stmt = db()->prepare('INSERT INTO mangas (title, slug, author, description, cover_image, release_date, is_featured, created_at, updated_at) VALUES (:t,:s,\'Uploader\',\'\',\'\',NULL,0,NOW(),NOW()) ON DUPLICATE KEY UPDATE updated_at=NOW()');
        $stmt->execute([':t'=>$mangaTitle, ':s'=>$slug]);
        $mid = (int)db()->query('SELECT id FROM mangas WHERE slug=' . db()->quote($slug))->fetchColumn();
        foreach ($chapters as $num => $files) {
            $stmt = db()->prepare('INSERT INTO chapters (manga_id, chapter_number, title, created_at) VALUES (:m,:n,\'\',NOW()) ON DUPLICATE KEY UPDATE title=title');
            $stmt->execute([':m'=>$mid, ':n'=>$num]);
            $cid = (int)db()->lastInsertId();
            if ($cid === 0) { $cid = (int)db()->query('SELECT id FROM chapters WHERE manga_id=' . (int)$mid . ' AND chapter_number=' . db()->quote($num))->fetchColumn(); }
            sort($files, SORT_NATURAL | SORT_FLAG_CASE);
            $p = 1;
            foreach ($files as $src) {
                $ext = pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg';
                $name = 'up_' . $cid . '_' . $p . '_' . bin2hex(random_bytes(2)) . '.' . strtolower($ext);
                copy($src, UPLOADS_PATH . '/' . $name);
                db()->prepare('INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES (:c,:p,:i)')->execute([':c'=>$cid, ':p'=>$p, ':i'=>$name]);
                $p++;
            }
        }
        db()->prepare('UPDATE mangas SET updated_at = NOW() WHERE id=:id')->execute([':id'=>$mid]);
    }

    echo "Upload processed\n";
    exit(0);
}

cli_error("Usage:\n  php manga_retriever.php --mangadex <uuid> --lang en\n  php manga_retriever.php --upload /path/to/file.zip\n");