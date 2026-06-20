<?php

declare(strict_types=1);

$headingLevel = $headingLevel ?? 2;
$showQuoteLink = $showQuoteLink ?? false;
$categoria = $categorias[$produto['categoria']] ?? ['nome' => 'Produto', 'icone' => 'toolbox'];
$headingTag = $headingLevel === 3 ? 'h3' : 'h2';

// Badge de estoque conforme condição (validação condicional).
$estoque = (string) ($produto['estoque'] ?? '');
$estoqueClasse = stripos($estoque, 'pronta') !== false ? 'b-ok' : 'b-wait';

// Preço pode vir como número (banco) ou string (fallback).
$precoTexto = is_numeric($produto['preco'] ?? null)
    ? formatar_preco((float) $produto['preco'])
    : (string) ($produto['preco'] ?? 'Sob consulta');

$icone = icone_categoria($categoria['icone'] ?? 'toolbox');
?>

<article class="card prod">
    <div class="prod__media">
        <i class="bi <?= e($icone) ?>"></i>
        <span class="badge <?= e($estoqueClasse) ?>"><?= e($estoque) ?></span>
    </div>
    <div class="prod__body">
        <span class="prod__tag"><?= e($categoria['nome']) ?></span>
        <<?= $headingTag ?>><?= e($produto['nome']) ?></<?= $headingTag ?>>
        <p><?= e($produto['descricao']) ?></p>
        <div class="prod__foot">
            <div class="prod__price">
                <b><?= e($precoTexto) ?></b>
                <span>por <?= e($produto['unidade']) ?></span>
            </div>
            <?php if ($showQuoteLink): ?>
                <a class="prod__cta" href="orcamento.php?produto=<?= e((string) $produto['id']) ?>"
                   aria-label="Orçar <?= e($produto['nome']) ?>">
                    <i class="bi bi-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>
