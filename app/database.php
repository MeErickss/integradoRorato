<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Retorna uma conexão PDO única (singleton) com o banco MySQL.
 *
 * Em caso de falha na conexão, devolve null — assim as páginas
 * conseguem cair para o catálogo em array (fallback) sem quebrar.
 *
 * @return PDO|null
 */
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
        // Não exibe credenciais nem detalhes sensíveis ao usuário.
        error_log('Falha ao conectar no banco: ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}

/**
 * Indica se há conexão ativa com o banco de dados.
 */
function db_online(): bool
{
    return db() instanceof PDO;
}
