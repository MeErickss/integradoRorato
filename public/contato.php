<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/repository.php';

$enviado = false;
$sucesso = false;
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enviado = true;

    $nome     = trim((string) ($_POST['nome'] ?? ''));
    $contato  = trim((string) ($_POST['contato'] ?? ''));
    $assunto  = trim((string) ($_POST['assunto'] ?? ''));
    $mensagem = trim((string) ($_POST['mensagem'] ?? ''));

    if ($nome === '')     { $erros[] = 'Informe o seu nome.'; }
    if ($contato === '')  { $erros[] = 'Informe um telefone ou e-mail.'; }
    if ($assunto === '')  { $erros[] = 'Informe o assunto.'; }
    if ($mensagem === '') { $erros[] = 'Escreva a sua mensagem.'; }

    if ($erros === []) {
        repo_salvar_mensagem([
            'nome'     => $nome,
            'contato'  => $contato,
            'assunto'  => $assunto,
            'mensagem' => $mensagem,
        ]);
        $sucesso = true;
    }
}

$pageTitle = 'Contato';
$pageDescription = 'Canais de atendimento da Construcoes Rorato.';
$activePage = 'contato';

require __DIR__ . '/../app/templates/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb rorato-breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contato</li>
            </ol>
        </nav>
        <p class="eyebrow">Contato</p>
        <h1>Fale com a Construções Rorato</h1>
        <p>Envie sua dúvida sobre produto, retirada, entrega ou orçamento.</p>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="contact-layout">
            <form class="panel" method="post">
                <?php if ($sucesso): ?>
                    <div class="alert alert-success" role="alert">
                        ✓ Mensagem enviada com sucesso. O retorno será feito pelo contato informado.
                    </div>
                <?php elseif ($enviado && $erros !== []): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Revise os campos:</strong>
                        <ul>
                            <?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <p class="panel-title"><i class="bi bi-send"></i> Enviar mensagem</p>

                <div class="field">
                    <input id="nome" name="nome" type="text" required placeholder=" " value="<?= e($_POST['nome'] ?? '') ?>">
                    <label for="nome">Nome completo</label>
                </div>
                <div class="field">
                    <input id="contato" name="contato" type="text" required placeholder=" " value="<?= e($_POST['contato'] ?? '') ?>">
                    <label for="contato">Telefone ou e-mail</label>
                </div>
                <div class="field">
                    <input id="assunto" name="assunto" type="text" required placeholder=" " value="<?= e($_POST['assunto'] ?? '') ?>">
                    <label for="assunto">Assunto</label>
                </div>
                <div class="field">
                    <textarea id="mensagem" name="mensagem" required placeholder=" "><?= e($_POST['mensagem'] ?? '') ?></textarea>
                    <label for="mensagem">Mensagem</label>
                </div>

                <button class="btn btn--primary btn--block btn--lg" type="submit">
                    <i class="bi bi-send"></i> Enviar mensagem
                </button>
            </form>

            <div class="contact-info">
                <div class="contact-highlight">
                    <i class="bi bi-clock-history"></i>
                    <div><b>Resposta rápida</b><span>Retorno em até 2 horas úteis</span></div>
                </div>
                <div class="contact-card">
                    <i class="bi bi-telephone"></i>
                    <div><span>Telefone / WhatsApp</span><b><?= e(APP_PHONE) ?></b></div>
                </div>
                <div class="contact-card">
                    <i class="bi bi-envelope"></i>
                    <div><span>E-mail</span><b><?= e(APP_EMAIL) ?></b></div>
                </div>
                <div class="contact-card">
                    <i class="bi bi-geo-alt"></i>
                    <div><span>Endereço</span><b><?= e(APP_ADDRESS) ?></b></div>
                </div>
                <a class="btn btn--accent btn--block" href="<?= e(whatsapp_link('Ola! Vim pelo site da Construcoes Rorato.')) ?>">
                    <i class="bi bi-whatsapp"></i> Abrir WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════ FAQ (Accordion Bootstrap) ═══════════ -->
<section class="section section--soft">
    <div class="wrap">
        <div class="section-head center reveal">
            <p class="eyebrow">Dúvidas frequentes</p>
            <h2>Perguntas comuns do atendimento</h2>
        </div>

        <div class="accordion rorato-accordion reveal" id="faqAccordion">
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        Vocês entregam o material na obra?
                    </button>
                </h3>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Sim. Combinamos a entrega conforme o bairro e o volume do pedido — informe o endereço no orçamento.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Como recebo o preço dos produtos?
                    </button>
                </h3>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Os preços de referência ficam no catálogo. Para a condição final, faça um orçamento e confirmamos a disponibilidade.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        Posso retirar no balcão?
                    </button>
                </h3>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Pode sim. Itens marcados como “Pronta entrega” ficam disponíveis para retirada imediata.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../app/templates/footer.php'; ?>
