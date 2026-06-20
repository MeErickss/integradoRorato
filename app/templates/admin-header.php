<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Painel';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
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
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
    <header class="admin-bar">
        <div class="wrap admin-bar__inner">
            <a class="admin-bar__logo" href="admin.php" aria-label="<?= e(APP_NAME) ?>">
                <img src="<?= e(asset('img/logo-rorato.png')) ?>" alt="<?= e(APP_NAME) ?>">
            </a>
            <span class="admin-bar__tag"><i class="bi bi-shield-lock"></i> Painel administrativo</span>
            <div class="admin-bar__right">
                <button class="nav__theme" id="themeToggle" type="button" aria-label="Alternar tema" title="Alternar tema">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <a class="btn btn--outline" href="index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver site</a>
                <a class="btn btn--primary" href="admin-logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </div>
        </div>
    </header>

    <main class="wrap admin-main">
