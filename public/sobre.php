<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/repository.php';

$totalProdutos = count(repo_produtos());
$totalCategorias = count(repo_categorias());

$pageTitle = 'Empresa';
$pageDescription = 'Conheca a Construcoes Rorato e sua proposta para obras residenciais.';
$activePage = 'sobre';

require __DIR__ . '/../app/templates/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb rorato-breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Empresa</li>
            </ol>
        </nav>
        <p class="eyebrow">Empresa</p>
        <h1>Atendimento próximo, sem complicação para a sua obra.</h1>
        <p>A Construções Rorato atende quem precisa comprar material para construir, reformar ou renovar ambientes residenciais.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="about-layout">
            <div class="about-copy reveal">
                <p class="eyebrow">Quem somos</p>
                <h2>Da lista de obra ao balcão</h2>
                <p>
                    O site foi pensado para facilitar a primeira conversa com o cliente: apresentar as linhas de
                    produtos, abrir um canal de orçamento e organizar os dados que viram pedidos e histórico de compras.
                </p>
                <p>
                    A identidade visual usa o azul da marca Rorato com detalhes em laranja para reforçar a energia
                    do universo da construção — moderna, direta e confiável.
                </p>

                <div class="about-stats">
                    <div class="about-stat"><b><?= e((string) $totalCategorias) ?></b><span>Linhas de produto</span></div>
                    <div class="about-stat"><b><?= e((string) $totalProdutos) ?>+</b><span>Itens no catálogo</span></div>
                    <div class="about-stat"><b>2h</b><span>Tempo de retorno</span></div>
                </div>

                <div class="about-actions">
                    <a class="btn btn--primary" href="orcamento.php">Solicitar orçamento <i class="bi bi-arrow-right"></i></a>
                    <a class="btn btn--outline" href="produtos.php">Ver catálogo</a>
                </div>
            </div>

            <div class="values">
                <div class="value reveal" data-d="1">
                    <i class="bi bi-lightning-charge"></i>
                    <div><h3>Agilidade</h3><p>Contato direto para tirar dúvidas sobre produto, quantidade e retirada.</p></div>
                </div>
                <div class="value reveal" data-d="2">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <div><h3>Variedade</h3><p>Cimento, massa, tinta e ferramentas no mesmo catálogo, para toda etapa da obra.</p></div>
                </div>
                <div class="value reveal" data-d="3">
                    <i class="bi bi-clipboard-check"></i>
                    <div><h3>Organização</h3><p>Informações claras para comparar itens e montar o orçamento antes de comprar.</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="wrap">
        <div class="cta reveal">
            <div class="cta__grid"></div>
            <div>
                <p class="eyebrow" style="color:#ffd9b8">Vamos começar?</p>
                <h2>Sua próxima obra começa com o material certo.</h2>
            </div>
            <a class="btn btn--light btn--lg" href="contato.php">Falar com a gente <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../app/templates/footer.php'; ?>
