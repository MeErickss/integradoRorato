<?php

declare(strict_types=1);

/* ── App ─────────────────────────────────────────── */
const APP_NAME = 'Construções Rorato';
const APP_TAGLINE = 'Tecnologia na sua obra';
const APP_DOMAIN = 'rorato.local';
const APP_PHONE = '(54) 99999-2026';
const APP_WHATSAPP = '5554999992026';
const APP_EMAIL = 'atendimento@rorato.local';
const APP_ADDRESS = 'Rua das Obras, 120 - Centro';

/* ── Banco de dados (detecta local x hospedagem) ─── */
$rorato_local =
    PHP_SAPI === 'cli-server' ||
    in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', 'rorato.local'], true) ||
    in_array($_SERVER['SERVER_ADDR'] ?? '', ['127.0.0.1', '::1'], true);

if ($rorato_local) {
    define('DB_HOST', '127.0.0.1');   // IP fixo
    define('DB_PORT', '3306');
    define('DB_NAME', 'rorato_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', 'sql204.ezyro.com');
    define('DB_PORT', '3306');
    define('DB_NAME', 'ezyro_42231415_rorato_db');
    define('DB_USER', 'ezyro_42231415');
    define('DB_PASS', '');   // senha do banco: preencher no host
}

const DB_CHARSET = 'utf8mb4';

/* ── Admin ───────────────────────────────────────── */
const ADMIN_USER = 'admin';
const ADMIN_PASSWORD = 'rorato2026';

/* ── Helpers ─────────────────────────────────────── */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return 'assets/' . ltrim($path, '/');
}

function page_url(string $path = 'index.php'): string
{
    return $path;
}

function is_active(string $current, string $expected): string
{
    return $current === $expected ? 'is-active' : '';
}

function whatsapp_link(string $message): string
{
    return 'https://wa.me/' . APP_WHATSAPP . '?text=' . rawurlencode($message);
}

function icone_categoria(string $token): string
{
    $mapa = [
        'factory' => 'bi-bricks',
        'layers'  => 'bi-layers-half',
        'paint'   => 'bi-bucket',
        'toolbox' => 'bi-tools',
    ];

    return $mapa[$token] ?? 'bi-box-seam';
}
