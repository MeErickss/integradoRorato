<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/repository.php';

$categorias = repo_categorias();
$produtos   = repo_produtos();

$categoriaAtual = (string) ($_GET['categoria'] ?? 'todos');
$busca = trim((string) ($_GET['q'] ?? ''));

$produtosFiltrados = repo_filtrar_produtos($produtos, $categoriaAtual, $busca);
$nomeCategoria = $categorias[$categoriaAtual]['nome'] ?? 'todas as linhas';

$pageTitle = 'Produtos';
$pageDescription = 'Catalogo de cimentos, massas, tintas e ferramentas para obras.';
$activePage = 'produtos';

require __DIR__ . '/../app/templates/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb rorato-breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Produtos</li>
            </ol>
        </nav>
        <p class="eyebrow">Catálogo</p>
        <h1>Produtos para construção, pintura e acabamento</h1>
        <p>Filtre por linha ou pesquise um item para montar o seu orçamento.</p>
    </div>
</section>

<section class="filters">
    <div class="wrap">
        <form method="get" class="filter-form">
            <div class="f-group">
                <label for="categoria"><i class="bi bi-funnel"></i> Linha</label>
                <select name="categoria" id="categoria">
                    <option value="todos" <?= $categoriaAtual === 'todos' ? 'selected' : '' ?>>Todas</option>
                    <?php foreach ($categorias as $slug => $categoria): ?>
                        <option value="<?= e($slug) ?>" <?= $categoriaAtual === $slug ? 'selected' : '' ?>>
                            <?= e($categoria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="f-search">
                <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Buscar: cimento, tinta, massa...">
            </div>
            <button class="btn btn--primary" type="submit"><i class="bi bi-search"></i> Filtrar</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="results-bar">
            <b><?= count($produtosFiltrados) ?></b>
            <span>produto(s) em <em><?= e($nomeCategoria) ?></em></span>
        </div>

        <?php if ($produtosFiltrados === []): ?>
            <div class="empty">
                <i class="bi bi-search"></i>
                <h2>Nenhum produto encontrado</h2>
                <p>Tente remover os filtros ou peça um orçamento pelo atendimento.</p>
                <a class="btn btn--primary" href="produtos.php">Limpar filtros</a>
            </div>
        <?php else: ?>
            <div class="prod-grid prod-grid--3">
                <?php $i = 0; foreach ($produtosFiltrados as $produto): ?>
                    <div class="reveal" data-d="<?= e((string) ($i % 3)) ?>">
                        <?php
                            $headingLevel = 2;
                            $showQuoteLink = true;
                            require __DIR__ . '/../app/templates/product-card.php';
                        ?>
                    </div>
                <?php $i++; endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../app/templates/footer.php'; ?>
