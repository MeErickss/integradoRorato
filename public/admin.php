<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/repository.php';

if (empty($_SESSION['admin'])) {
    header('Location: admin-login.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* ── Ações do CRUD de produtos (POST) ────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = (string) ($_POST['acao'] ?? '');

    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Sessão inválida. Tente novamente.'];
        header('Location: admin.php?tab=produtos');
        exit;
    }

    if ($acao === 'excluir') {
        $resultado = repo_excluir_produto((int) ($_POST['id'] ?? 0));
        $_SESSION['flash'] = $resultado['ok']
            ? ['tipo' => 'success', 'msg' => 'Produto excluído com sucesso.']
            : ['tipo' => 'danger', 'msg' => (string) $resultado['erro']];
    } elseif ($acao === 'criar' || $acao === 'atualizar') {
        $nome      = trim((string) ($_POST['nome'] ?? ''));
        $idCat     = (int) ($_POST['id_categoria'] ?? 0);
        $unidade   = trim((string) ($_POST['unidade'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $estoque   = trim((string) ($_POST['estoque'] ?? ''));
        $precoRaw  = trim((string) ($_POST['preco_base'] ?? ''));
        $destaque  = isset($_POST['destaque']) ? 1 : 0;
        $ativo     = isset($_POST['ativo']) ? 1 : 0;

        $preco = null;
        if ($precoRaw !== '') {
            $tmp = preg_replace('/[^0-9.,]/', '', $precoRaw);
            $tmp = str_replace(',', '.', (string) $tmp);
            $preco = $tmp !== '' ? (float) $tmp : null;
        }

        if ($nome === '' || $idCat <= 0 || $unidade === '') {
            $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Preencha pelo menos nome, categoria e unidade.'];
        } else {
            $dados = [
                'id_categoria' => $idCat,
                'nome'         => $nome,
                'descricao'    => $descricao,
                'unidade'      => $unidade,
                'preco_base'   => $preco,
                'estoque'      => $estoque !== '' ? $estoque : 'Consultar',
                'destaque'     => $destaque,
                'ativo'        => $ativo,
            ];

            if ($acao === 'criar') {
                $ok = repo_criar_produto($dados);
                $_SESSION['flash'] = $ok
                    ? ['tipo' => 'success', 'msg' => 'Produto criado com sucesso.']
                    : ['tipo' => 'danger', 'msg' => 'Não foi possível criar o produto.'];
            } else {
                $ok = repo_atualizar_produto((int) ($_POST['id'] ?? 0), $dados);
                $_SESSION['flash'] = $ok
                    ? ['tipo' => 'success', 'msg' => 'Produto atualizado com sucesso.']
                    : ['tipo' => 'danger', 'msg' => 'Não foi possível atualizar o produto.'];
            }
        }
    }

    header('Location: admin.php?tab=produtos');
    exit;
}

/* ── Dados (GET) ──────────────────────────────────── */
$orcamentos     = repo_listar_orcamentos();
$mensagens      = repo_listar_mensagens();
$produtosAdmin  = repo_listar_produtos_admin();
$categoriasLista = repo_categorias_lista();
$bancoOnline    = db_online();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$tab = (string) ($_GET['tab'] ?? 'orc');
if (!in_array($tab, ['orc', 'msg', 'produtos'], true)) {
    $tab = 'orc';
}

$totalItens = array_sum(array_map(static function (array $o): int {
    return count($o['itens']);
}, $orcamentos));

$pageTitle = 'Painel';
require __DIR__ . '/../app/templates/admin-header.php';
?>

<div class="admin-head">
    <p class="eyebrow">Visão geral</p>
    <h1>Pedidos, mensagens e produtos</h1>
</div>

<?php if (!$bancoOnline): ?>
    <div class="alert alert-danger" role="alert">
        Banco de dados indisponível. Inicie o MySQL para visualizar e gerenciar os registros.
    </div>
<?php endif; ?>

<div class="admin-stats">
    <div class="admin-stat">
        <i class="bi bi-receipt"></i>
        <div><b><?= e((string) count($orcamentos)) ?></b><span>Orçamentos</span></div>
    </div>
    <div class="admin-stat">
        <i class="bi bi-chat-left-text"></i>
        <div><b><?= e((string) count($mensagens)) ?></b><span>Mensagens</span></div>
    </div>
    <div class="admin-stat">
        <i class="bi bi-box-seam"></i>
        <div><b><?= e((string) count($produtosAdmin)) ?></b><span>Produtos</span></div>
    </div>
    <div class="admin-stat">
        <i class="bi bi-bag-check"></i>
        <div><b><?= e((string) $totalItens) ?></b><span>Itens orçados</span></div>
    </div>
</div>

<ul class="nav nav-tabs admin-tabs" id="adminTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $tab === 'orc' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#pane-orc" type="button" role="tab">
            <i class="bi bi-receipt"></i> Orçamentos (<?= e((string) count($orcamentos)) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $tab === 'msg' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#pane-msg" type="button" role="tab">
            <i class="bi bi-chat-left-text"></i> Mensagens (<?= e((string) count($mensagens)) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $tab === 'produtos' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#pane-prod" type="button" role="tab">
            <i class="bi bi-box-seam"></i> Produtos (<?= e((string) count($produtosAdmin)) ?>)
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- ===== Orçamentos ===== -->
    <div class="tab-pane fade <?= $tab === 'orc' ? 'show active' : '' ?>" id="pane-orc" role="tabpanel">
        <?php if ($orcamentos === []): ?>
            <div class="empty"><i class="bi bi-receipt"></i><h2>Nenhum orçamento ainda</h2><p>Os orçamentos enviados pelo site aparecem aqui.</p></div>
        <?php else: ?>
            <?php foreach ($orcamentos as $orc): ?>
                <article class="order-card">
                    <div class="order-card__top">
                        <span class="order-card__id"><i class="bi bi-hash"></i>Orçamento <?= e((string) $orc['id_orcamento']) ?></span>
                        <span class="badge b-ok"><?= e((string) $orc['status']) ?></span>
                        <span class="order-card__date"><i class="bi bi-calendar3"></i> <?= e(formatar_data($orc['criado_em'])) ?></span>
                    </div>
                    <div class="order-card__meta">
                        <div><span>Cliente</span><b><?= e((string) $orc['nome']) ?></b></div>
                        <div><span>Telefone</span><b><?= e((string) $orc['telefone']) ?></b></div>
                        <?php if (!empty($orc['email'])): ?><div><span>E-mail</span><b><?= e((string) $orc['email']) ?></b></div><?php endif; ?>
                    </div>
                    <?php if ($orc['itens'] !== []): ?>
                        <div class="order-items">
                            <?php foreach ($orc['itens'] as $item): ?>
                                <div class="order-item"><i class="bi bi-box"></i><span><?= e((string) $item['produto']) ?></span><span class="qt"><?= e((string) (float) $item['quantidade']) ?> <?= e((string) $item['unidade']) ?></span></div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="order-empty-items">Sem item específico (produto sob consulta).</p>
                    <?php endif; ?>
                    <?php if (!empty($orc['observacoes'])): ?>
                        <div class="order-obs"><i class="bi bi-chat-quote"></i> <?= e((string) $orc['observacoes']) ?></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ===== Mensagens ===== -->
    <div class="tab-pane fade <?= $tab === 'msg' ? 'show active' : '' ?>" id="pane-msg" role="tabpanel">
        <?php if ($mensagens === []): ?>
            <div class="empty"><i class="bi bi-chat-left-text"></i><h2>Nenhuma mensagem ainda</h2><p>As mensagens do formulário de contato aparecem aqui.</p></div>
        <?php else: ?>
            <?php foreach ($mensagens as $msg): ?>
                <article class="msg-card">
                    <div class="msg-card__top">
                        <div>
                            <span class="msg-card__subject"><?= e((string) $msg['assunto']) ?></span>
                            <span class="msg-card__from">de <b><?= e((string) $msg['nome']) ?></b> · <?= e((string) $msg['contato']) ?></span>
                        </div>
                        <span class="order-card__date"><i class="bi bi-calendar3"></i> <?= e(formatar_data($msg['enviado_em'])) ?></span>
                    </div>
                    <p class="msg-card__body"><?= nl2br(e((string) $msg['mensagem'])) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ===== Produtos (CRUD) ===== -->
    <div class="tab-pane fade <?= $tab === 'produtos' ? 'show active' : '' ?>" id="pane-prod" role="tabpanel">
        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e($flash['tipo']) ?>" role="alert"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <div class="admin-toolbar">
            <span class="count"><?= e((string) count($produtosAdmin)) ?> produto(s) cadastrado(s)</span>
            <button class="btn btn--primary" id="btnNovoProduto" data-bs-toggle="modal" data-bs-target="#modalProduto">
                <i class="bi bi-plus-lg"></i> Novo produto
            </button>
        </div>

        <?php if ($produtosAdmin === []): ?>
            <div class="empty"><i class="bi bi-box-seam"></i><h2>Nenhum produto cadastrado</h2><p>Clique em “Novo produto” para começar.</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Destaque</th>
                            <th>Status</th>
                            <th style="text-align:right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtosAdmin as $p): ?>
                            <tr>
                                <td>
                                    <div class="admin-prod-name"><?= e((string) $p['nome']) ?></div>
                                    <div class="admin-prod-cat"><?= e((string) $p['categoria_nome']) ?></div>
                                </td>
                                <td class="admin-price"><?= e(formatar_preco($p['preco_base'] !== null ? (float) $p['preco_base'] : null)) ?></td>
                                <td><?= e((string) $p['estoque']) ?></td>
                                <td>
                                    <?php if ((int) $p['destaque'] === 1): ?>
                                        <span class="flag"><i class="bi bi-star-fill star-on"></i></span>
                                    <?php else: ?>
                                        <span class="flag flag-off"><i class="bi bi-star"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) $p['ativo'] === 1): ?>
                                        <span class="flag flag-on"><i class="bi bi-check-circle-fill"></i> Ativo</span>
                                    <?php else: ?>
                                        <span class="flag flag-off"><i class="bi bi-slash-circle"></i> Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <button class="icon-btn btn-edit" title="Editar"
                                            data-id="<?= e((string) $p['id_produto']) ?>"
                                            data-nome="<?= e((string) $p['nome']) ?>"
                                            data-categoria="<?= e((string) $p['id_categoria']) ?>"
                                            data-descricao="<?= e((string) $p['descricao']) ?>"
                                            data-unidade="<?= e((string) $p['unidade']) ?>"
                                            data-preco="<?= e($p['preco_base'] !== null ? (string) $p['preco_base'] : '') ?>"
                                            data-estoque="<?= e((string) $p['estoque']) ?>"
                                            data-destaque="<?= e((string) $p['destaque']) ?>"
                                            data-ativo="<?= e((string) $p['ativo']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalProduto">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" onsubmit="return confirm('Excluir este produto? Esta ação não pode ser desfeita.');" style="margin:0">
                                            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="acao" value="excluir">
                                            <input type="hidden" name="id" value="<?= e((string) $p['id_produto']) ?>">
                                            <button class="icon-btn icon-btn--danger" type="submit" title="Excluir"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== Modal: criar/editar produto ===== -->
<div class="modal fade" id="modalProduto" tabindex="-1" aria-labelledby="modalProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post" id="formProduto">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProdutoLabel">Novo produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="acao" id="prodAcao" value="criar">
                    <input type="hidden" name="id" id="prodId" value="">

                    <div class="field">
                        <input type="text" id="prodNome" name="nome" required placeholder=" ">
                        <label for="prodNome">Nome do produto</label>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <select id="prodCategoria" name="id_categoria" required>
                                <option value=""></option>
                                <?php foreach ($categoriasLista as $cat): ?>
                                    <option value="<?= e((string) $cat['id_categoria']) ?>"><?= e((string) $cat['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="prodCategoria">Categoria</label>
                        </div>
                        <div class="field">
                            <input type="text" id="prodUnidade" name="unidade" required placeholder=" ">
                            <label for="prodUnidade">Unidade (saco, lata, unidade...)</label>
                        </div>
                    </div>

                    <div class="field">
                        <textarea id="prodDescricao" name="descricao" placeholder=" "></textarea>
                        <label for="prodDescricao">Descrição</label>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <input type="text" id="prodPreco" name="preco_base" placeholder=" ">
                            <label for="prodPreco">Preço (ex.: 38,90 — vazio = sob consulta)</label>
                        </div>
                        <div class="field">
                            <input type="text" id="prodEstoque" name="estoque" placeholder=" ">
                            <label for="prodEstoque">Estoque (ex.: Pronta entrega)</label>
                        </div>
                    </div>

                    <div class="switches">
                        <label class="switch"><input type="checkbox" id="prodDestaque" name="destaque" value="1"> <i class="bi bi-star"></i> Destaque na home</label>
                        <label class="switch"><input type="checkbox" id="prodAtivo" name="ativo" value="1" checked> <i class="bi bi-eye"></i> Ativo (visível no site)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn--primary"><i class="bi bi-check-lg"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../app/templates/admin-footer.php'; ?>
