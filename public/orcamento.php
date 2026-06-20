<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/repository.php';

$produtos = repo_produtos();

$produtoSelecionado = (int) ($_GET['produto'] ?? $_POST['produto'] ?? 0);

$enviado = false;
$sucesso = false;
$erros = [];
$resumo = [];
$idOrcamento = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enviado = true;

    $nome       = trim((string) ($_POST['nome'] ?? ''));
    $telefone   = trim((string) ($_POST['telefone'] ?? ''));
    $email      = trim((string) ($_POST['email'] ?? ''));
    $produtoId  = (int) ($_POST['produto'] ?? 0);
    $quantidade = trim((string) ($_POST['quantidade'] ?? ''));
    $mensagem   = trim((string) ($_POST['mensagem'] ?? ''));

    if ($nome === '')       { $erros[] = 'Informe o seu nome.'; }
    if ($telefone === '')   { $erros[] = 'Informe um telefone ou WhatsApp.'; }
    if ($quantidade === '') { $erros[] = 'Informe a quantidade desejada.'; }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O e-mail informado não é válido.';
    }

    $produto = repo_produto_por_id($produtos, $produtoId);
    $produtoNome = $produto !== null ? $produto['nome'] : 'Produto sob consulta';
    $unidade = $produto !== null ? $produto['unidade'] : '';

    if ($erros === []) {
        $resultado = repo_salvar_orcamento([
            'nome'        => $nome,
            'telefone'    => $telefone,
            'email'       => $email,
            'id_produto'  => $produtoId,
            'quantidade'  => $quantidade,
            'unidade'     => $unidade,
            'observacoes' => $mensagem,
        ]);

        $sucesso = true;
        $idOrcamento = $resultado['ok'] ? $resultado['id'] : null;

        $resumo = [
            'nome'       => $nome,
            'telefone'   => $telefone,
            'produto'    => $produtoNome,
            'quantidade' => $quantidade,
            'mensagem'   => $mensagem,
        ];
    }
}

$pageTitle = 'Orçamento';
$pageDescription = 'Formulario dinamico para solicitar orcamentos de materiais.';
$activePage = 'orcamento';

require __DIR__ . '/../app/templates/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb rorato-breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Orçamento</li>
            </ol>
        </nav>
        <p class="eyebrow">Orçamento</p>
        <h1>Solicite uma cotação rápida</h1>
        <p>Informe o material, a quantidade aproximada e o melhor contato para retorno.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="form-layout">
            <form class="panel" method="post" id="quoteForm">
                <?php if ($enviado && $erros !== []): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Revise os campos:</strong>
                        <ul>
                            <?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="fsteps">
                    <div class="fstep active"><div class="fstep__n">1</div><span>Você</span></div>
                    <div class="fstep"><div class="fstep__n">2</div><span>Produto</span></div>
                    <div class="fstep"><div class="fstep__n">3</div><span>Detalhes</span></div>
                </div>

                <div class="field">
                    <input id="nome" name="nome" type="text" required placeholder=" " value="<?= e($_POST['nome'] ?? '') ?>">
                    <label for="nome">Nome completo</label>
                </div>

                <div class="field-row">
                    <div class="field">
                        <input id="telefone" name="telefone" type="tel" required placeholder=" " value="<?= e($_POST['telefone'] ?? '') ?>">
                        <label for="telefone">Telefone / WhatsApp</label>
                    </div>
                    <div class="field">
                        <input id="email" name="email" type="email" placeholder=" " value="<?= e($_POST['email'] ?? '') ?>">
                        <label for="email">E-mail (opcional)</label>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <select id="produto" name="produto" required>
                            <option value=""></option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?= e((string) $produto['id']) ?>" <?= $produtoSelecionado === $produto['id'] ? 'selected' : '' ?>>
                                    <?= e($produto['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="0">Outro produto</option>
                        </select>
                        <label for="produto">Produto</label>
                    </div>
                    <div class="field">
                        <input id="quantidade" name="quantidade" type="text" required placeholder=" " value="<?= e($_POST['quantidade'] ?? '') ?>">
                        <label for="quantidade">Quantidade</label>
                    </div>
                </div>

                <div class="field">
                    <textarea id="mensagem" name="mensagem" placeholder=" "><?= e($_POST['mensagem'] ?? '') ?></textarea>
                    <label for="mensagem">Observações (opcional)</label>
                </div>

                <button class="btn btn--primary btn--block btn--lg" type="submit">
                    <i class="bi bi-send"></i> Enviar solicitação
                </button>
            </form>

            <aside class="panel panel--accent">
                <?php if ($sucesso): ?>
                    <div class="alert alert-success" role="alert">
                        ✓ Solicitação recebida!
                        <?php if ($idOrcamento !== null): ?>
                            Protocolo nº <strong><?= e((string) $idOrcamento) ?></strong>.
                        <?php endif; ?>
                    </div>
                    <h2><?= e($resumo['nome'] ?: 'Cliente') ?>, seu pedido foi registrado.</h2>
                    <p class="summary-info">Entraremos em contato em breve pelo número informado.</p>
                    <dl>
                        <dt>Contato</dt><dd><?= e($resumo['telefone']) ?></dd>
                        <dt>Produto</dt><dd><?= e($resumo['produto']) ?></dd>
                        <dt>Qtd.</dt><dd><?= e($resumo['quantidade']) ?></dd>
                        <?php if ($resumo['mensagem'] !== ''): ?>
                            <dt>Obs.</dt><dd><?= e($resumo['mensagem']) ?></dd>
                        <?php endif; ?>
                    </dl>
                <?php else: ?>
                    <div class="intro-block">
                        <i class="bi bi-chat-square-heart"></i>
                        <h2>Retorno humano</h2>
                        <p>Confirmamos estoque, unidade e a melhor condição para a obra.</p>
                    </div>
                    <div class="feature-list">
                        <div class="feature"><i class="bi bi-lightning-charge"></i><span>Resposta em até 2 horas úteis</span></div>
                        <div class="feature"><i class="bi bi-box-seam"></i><span>Estoque confirmado antes da venda</span></div>
                        <div class="feature"><i class="bi bi-truck"></i><span>Entrega combinada na obra</span></div>
                        <div class="feature"><i class="bi bi-shield-lock"></i><span>Seus dados ficam apenas conosco</span></div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../app/templates/footer.php'; ?>
