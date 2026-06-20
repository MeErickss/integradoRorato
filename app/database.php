<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/* ── Conexão PDO (singleton, com fallback) ───────── */
function db(): ?PDO
{
    static $pdo = null;
    static $tentou = false;

    if ($tentou) {
        return $pdo;
    }

    $tentou = true;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log('Falha ao conectar no banco: ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}

function db_online(): bool
{
    return db() instanceof PDO;
}
