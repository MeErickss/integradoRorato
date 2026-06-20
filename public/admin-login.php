<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/config.php';

if (!empty($_SESSION['admin'])) {
    header('Location: admin.php');
    exit;
}

$erro = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $senha   = (string) ($_POST['senha'] ?? '');

    $usuarioOk = hash_equals(ADMIN_USER, $usuario);
    $senhaOk   = hash_equals(ADMIN_PASSWORD, $senha);

    if ($usuarioOk && $senhaOk) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    }

    $erro = true;
}

$pageTitle = 'Entrar';
require __DIR__ . '/../app/templates/admin-header.php';
?>

<div class="login-wrap">
    <form class="login-card" method="post">
        <div class="login-card__logo">
            <img src="<?= e(asset('img/logo-rorato.png')) ?>" alt="<?= e(APP_NAME) ?>">
        </div>
        <h1 class="login-card__title">Painel administrativo</h1>
        <p class="login-card__sub">Acesse para ver os orçamentos e mensagens recebidos.</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert">
                Usuário ou senha inválidos.
            </div>
        <?php endif; ?>

        <div class="field">
            <input id="usuario" name="usuario" type="text" required placeholder=" " autofocus>
            <label for="usuario">Usuário</label>
        </div>
        <div class="field">
            <input id="senha" name="senha" type="password" required placeholder=" ">
            <label for="senha">Senha</label>
        </div>

        <button class="btn btn--primary btn--block btn--lg" type="submit">
            <i class="bi bi-box-arrow-in-right"></i> Entrar
        </button>

        <a class="login-card__back" href="index.php"><i class="bi bi-arrow-left"></i> Voltar ao site</a>
    </form>
</div>

<?php require __DIR__ . '/../app/templates/admin-footer.php'; ?>
