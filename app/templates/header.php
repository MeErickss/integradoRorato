<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'Materiais para construcao, reforma, pintura e acabamento.';
$activePage = $activePage ?? 'home';
$navItems = [
    'home'      => ['label' => 'Início',    'href' => 'index.php'],
    'produtos'  => ['label' => 'Produtos',  'href' => 'produtos.php'],
    'sobre'     => ['label' => 'Empresa',   'href' => 'sobre.php'],
    'contato'   => ['label' => 'Contato',   'href' => 'contato.php'],
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (!t) { t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
                document.documentElement.setAttribute('data-theme', t);
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
    </script>
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
    <link rel="icon" type="image/png" href="<?= e(asset('img/logo-rorato.png')) ?>">
    <link rel="apple-touch-icon" href="<?= e(asset('img/logo-rorato.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
    <header class="nav" id="siteNav">
        <div class="wrap nav__inner">
            <a class="nav__logo" href="<?= e(page_url()) ?>" aria-label="<?= e(APP_NAME) ?>">
                <img src="<?= e(asset('img/logo-rorato.png')) ?>" alt="<?= e(APP_NAME) ?>">
            </a>

            <nav class="nav__links" id="navLinks" aria-label="Menu principal">
                <?php foreach ($navItems as $key => $item): ?>
                    <a class="nav__link <?= $activePage === $key ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>">
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
                <a class="nav__cta" href="orcamento.php"><i class="bi bi-calculator"></i> Orçamento</a>
            </nav>

            <div class="nav__right">
                <button class="nav__theme" id="themeToggle" type="button" aria-label="Alternar tema claro/escuro" title="Alternar tema">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <button class="nav__toggle" id="navToggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="navLinks">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </header>

    <main>
