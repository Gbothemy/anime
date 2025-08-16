<?php
require_once __DIR__ . '/config.php';

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
        exit;
    }

    return $pdo;
}

function db_query(string $sql, array $params = []): PDOStatement {
    $stmt = getPDO()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_last_insert_id(): string {
    return getPDO()->lastInsertId();
}
?>