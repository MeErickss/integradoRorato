<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/repository.php';

$categorias = repo_categorias();
$produtos   = repo_produtos();

$destaques = array_values(array_filter($produtos, static function (array $produto): bool {
    return $produto['destaque'];
}));

$totalProdutos   = count($produtos);
$totalCategorias = count($categorias);
$heroProduto = $destaques[0] ?? ($produtos[0] ?? null);

$pageTitle = 'Início';
$pageDescription = 'Materiais para construcao, reforma e acabamento com atendimento proximo.';
$activePage = 'home';

require __DIR__ . '/../app/templates/header.php';
?>

<!-- ═══════════ HERO ═══════════ -->
<section class="hero">
    <div class="hero__grid"></div>
    <div class="wrap hero__inner">
        <div class="hero__content">
            <span class="hero__badge"><b><i class="bi bi-stars"></i> Novo</b> Catálogo online da Rorato</span>
            <h1 class="hero__title">Materiais <em>certos</em> para a sua <span class="gradient">obra sair do papel</span>.</h1>
            <p class="hero__lead">
                Cimentos, massas, tintas e ferramentas com atendimento direto para quem
                está construindo, reformando ou renovando a casa.
            </p>
            <div class="hero__actions">
                <a class="btn btn--accent btn--lg" href="orcamento.php">
                    Pedir orçamento <i class="bi bi-arrow-right"></i>
                </a>
                <button type="button" class="btn btn--ghost btn--lg" data-bs-toggle="modal" data-bs-target="#modalComo">
                    <i class="bi bi-play-circle"></i> Como funciona
                </button>
            </div>
            <div class="hero__trust">
                <span><i class="bi bi-check-circle-fill"></i> Pronta entrega</span>
                <span><i class="bi bi-check-circle-fill"></i> Entrega na obra</span>
                <span><i class="bi bi-check-circle-fill"></i> Atendimento direto</span>
            </div>
        </div>

        <div class="hero__visual">
            <div class="hero__glow"></div>
            <span class="hero__chip hero__chip--1"><i class="bi bi-truck"></i> Entrega rápida</span>
            <span class="hero__chip hero__chip--2"><i class="bi bi-shield-check"></i> Marcas confiáveis</span>
            <div class="hero__card">
                <div class="hero__card-logo">
                    <img src="<?= e(asset('img/logo-rorato.png')) ?>" alt="<?= e(APP_NAME) ?>">
                </div>
                <div class="hero__card-rows">
                    <div class="hero__card-row">
                        <span class="hero__card-ic"><i class="bi bi-box-seam"></i></span>
                        <div class="hero__card-tx">
                            <b><?= e($totalProdutos) ?> produtos</b>
                            <span>no catálogo atualizado</span>
                        </div>
                    </div>
                    <?php if ($heroProduto !== null): ?>
                    <div class="hero__card-row accent">
                        <span class="hero__card-ic"><i class="bi <?= e(icone_categoria($categorias[$heroProduto['categoria']]['icone'] ?? 'toolbox')) ?>"></i></span>
                        <div class="hero__card-tx">
                            <b><?= e($heroProduto['nome']) ?></b>
                            <span>destaque do balcão</span>
                        </div>
                        <span class="hero__card-price"><?= e(formatar_preco(is_numeric($heroProduto['preco']) ? (float) $heroProduto['preco'] : null)) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="hero__card-row">
                        <span class="hero__card-ic"><i class="bi bi-grid"></i></span>
                        <div class="hero__card-tx">
                            <b><?= e($totalCategorias) ?> categorias</b>
                            <span>cimento, massa, tinta e ferramentas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a class="scroll-ind" href="#catalogo" aria-label="Rolar para ver mais">
        <span>Role para ver mais</span>
        <span class="scroll-ind__mouse"><span></span></span>
    </a>
    <svg class="hero__wave" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
        <path fill="currentColor" d="M0,40 C360,90 1080,-10 1440,40 L1440,80 L0,80 Z"></path>
    </svg>
</section>

<!-- ═══════════ TRUST STRIP ═══════════ -->
<section class="strip">
    <div class="wrap strip__inner">
        <div class="strip__item"><i class="bi bi-truck"></i><div><b>Entrega na obra</b><span>Combine por bairro e volume</span></div></div>
        <div class="strip__item"><i class="bi bi-lightning-charge"></i><div><b>Pronta entrega</b><span>Itens disponíveis no balcão</span></div></div>
        <div class="strip__item"><i class="bi bi-chat-dots"></i><div><b>Atendimento direto</b><span>Resposta em até 2h úteis</span></div></div>
        <div class="strip__item"><i class="bi bi-patch-check"></i><div><b>Marcas confiáveis</b><span>Qualidade para a sua reforma</span></div></div>
    </div>
</section>

<!-- ═══════════ CATEGORIAS ═══════════ -->
<section id="catalogo" class="section">
    <div class="wrap">
        <div class="section-head reveal">
            <p class="eyebrow">Catálogo</p>
            <h2>Linhas para cada etapa da obra</h2>
            <p>Do alicerce ao acabamento, você encontra o material certo aqui.</p>
        </div>
        <div class="cat-grid">
            <?php $i = 0; foreach ($categorias as $slug => $categoria): ?>
                <?php
                    $quantidade = count(array_filter($produtos, static function (array $p) use ($slug): bool {
                        return $p['categoria'] === $slug;
                    }));
                ?>
                <a class="cat-card reveal" data-d="<?= e((string) ($i % 4)) ?>" href="produtos.php?categoria=<?= e($slug) ?>">
                    <span class="cat-card__ic"><i class="bi <?= e(icone_categoria($categoria['icone'])) ?>"></i></span>
                    <h3><?= e($categoria['nome']) ?></h3>
                    <p><?= e($categoria['descricao']) ?></p>
                    <span class="cat-card__link"><?= e($quantidade) ?> itens <i class="bi bi-arrow-right"></i></span>
                </a>
            <?php $i++; endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════ DESTAQUES (Carousel Bootstrap) ═══════════ -->
<section class="section section--soft">
    <div class="wrap">
        <div class="section-head center reveal">
            <p class="eyebrow">Em destaque</p>
            <h2>Os mais pedidos no balcão</h2>
        </div>

        <div id="carouselDestaques" class="carousel slide rorato-carousel reveal" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($destaques as $idx => $produto): ?>
                    <button type="button" data-bs-target="#carouselDestaques" data-bs-slide-to="<?= e((string) $idx) ?>"
                            class="<?= $idx === 0 ? 'active' : '' ?>" aria-label="Slide <?= e((string) ($idx + 1)) ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($destaques as $idx => $produto): ?>
                    <?php $cat = $categorias[$produto['categoria']] ?? ['nome' => 'Produto', 'icone' => 'toolbox']; ?>
                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                        <div class="ccard">
                            <div class="ccard__media"><i class="bi <?= e(icone_categoria($cat['icone'] ?? 'toolbox')) ?>"></i></div>
                            <div class="ccard__body">
                                <span class="ccard__tag"><?= e($cat['nome']) ?></span>
                                <h3><?= e($produto['nome']) ?></h3>
                                <p><?= e($produto['descricao']) ?></p>
                                <div class="ccard__foot">
                                    <span class="ccard__price"><?= e(formatar_preco(is_numeric($produto['preco']) ? (float) $produto['preco'] : null)) ?></span>
                                    <a class="btn btn--primary" href="orcamento.php?produto=<?= e((string) $produto['id']) ?>">
                                        Orçar este item <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestaques" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselDestaques" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Próximo</span>
            </button>
        </div>
    </div>
</section>

<!-- ═══════════ GRID DE PRODUTOS ═══════════ -->
<section class="section">
    <div class="wrap">
        <div class="section-head reveal">
            <p class="eyebrow">Mais pedidos</p>
            <h2>Prontos para retirada</h2>
            <p>Produtos com estoque disponível no balcão.</p>
        </div>
        <div class="prod-grid">
            <?php foreach (array_slice($destaques, 0, 4) as $produto): ?>
                <div class="reveal" data-d="<?= e((string) (array_search($produto, $destaques, true) % 4)) ?>">
                    <?php
                        $headingLevel = 3;
                        $showQuoteLink = true;
                        require __DIR__ . '/../app/templates/product-card.php';
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:44px" class="reveal">
            <a class="btn btn--outline btn--lg" href="produtos.php">Ver catálogo completo <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ═══════════ PROCESSO ═══════════ -->
<section class="section section--ink">
    <div class="wrap">
        <div class="section-head center reveal">
            <p class="eyebrow">Simples assim</p>
            <h2>Do pedido à retirada em 3 passos</h2>
        </div>
        <div class="steps">
            <div class="step reveal" data-d="1">
                <div class="step__n">1</div>
                <h3>Escolha os materiais</h3>
                <p>Navegue pelo catálogo ou peça direto pelo formulário de orçamento.</p>
            </div>
            <div class="step reveal" data-d="2">
                <div class="step__n">2</div>
                <h3>Receba a cotação</h3>
                <p>Confirmamos disponibilidade, unidade e a melhor condição para a obra.</p>
            </div>
            <div class="step reveal" data-d="3">
                <div class="step__n">3</div>
                <h3>Retire ou receba</h3>
                <p>Retire no balcão ou combine a entrega no endereço da sua obra.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════ CTA ═══════════ -->
<section class="section section--tight">
    <div class="wrap">
        <div class="cta reveal">
            <div class="cta__grid"></div>
            <div>
                <p class="eyebrow" style="color:#ffd9b8">Atendimento</p>
                <h2>Monte sua lista e receba o retorno para compra ou retirada.</h2>
                <p>Rápido, sem compromisso e com atendimento de quem entende de obra.</p>
            </div>
            <a class="btn btn--light btn--lg" href="orcamento.php">Começar orçamento <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- ═══════════ MODAL (Bootstrap) ═══════════ -->
<div class="modal fade" id="modalComo" tabindex="-1" aria-labelledby="modalComoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalComoLabel">Como pedir um orçamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <ol class="modal-steps">
                    <li><span><strong>Escolha os materiais</strong> no catálogo ou direto no formulário.</span></li>
                    <li><span><strong>Informe a quantidade</strong> e o melhor contato para retorno.</span></li>
                    <li><span><strong>Receba a cotação</strong> com disponibilidade e condição para a obra.</span></li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--outline" data-bs-dismiss="modal">Fechar</button>
                <a href="orcamento.php" class="btn btn--primary">Pedir orçamento</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../app/templates/footer.php'; ?>
