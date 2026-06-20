<?php

declare(strict_types=1);

const APP_NAME = 'Construções Rorato';
const APP_TAGLINE = 'Tecnologia na sua obra';
const APP_DOMAIN = 'rorato.local';
const APP_PHONE = '(54) 99999-2026';
const APP_WHATSAPP = '5554999992026';
const APP_EMAIL = 'atendimento@rorato.local';
const APP_ADDRESS = 'Rua das Obras, 120 - Centro';

/*
 * Configuração do banco de dados.
 *
 * DB_HOST usa um IP FIXO (em vez de "localhost") para atender ao
 * requisito de rede do projeto. Em ambiente com o banco em outra
 * máquina, basta trocar este IP pelo IP fixo do servidor MySQL.
 */
const DB_HOST = '127.0.0.1';   // IP fixo do servidor de banco
const DB_PORT = '3306';
const DB_NAME = 'rorato_db';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

/*
 * Acesso ao painel administrativo (/admin.php).
 * Troque o usuário e a senha abaixo antes de usar de verdade.
 */
const ADMIN_USER = 'admin';
const ADMIN_PASSWORD = 'rorato2026';

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

/**
 * Converte o token de ícone da categoria (armazenado no banco/array)
 * em uma classe de Bootstrap Icons.
 */
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
