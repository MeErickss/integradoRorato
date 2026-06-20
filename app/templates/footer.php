    </main>

    <footer class="footer">
        <div class="wrap">
            <div class="footer__top">
                <div class="footer__brand">
                    <a href="index.php" class="footer__logo">
                        <img src="<?= e(asset('img/logo-rorato.png')) ?>" alt="<?= e(APP_NAME) ?>">
                    </a>
                    <p>Materiais para construção, reforma e acabamento com atendimento direto, preço justo e entrega na obra.</p>
                    <a class="btn btn--accent" href="<?= e(whatsapp_link('Ola! Quero um orcamento da Construcoes Rorato.')) ?>">
                        <i class="bi bi-whatsapp"></i> Fale no WhatsApp
                    </a>
                </div>

                <div class="footer__col">
                    <h4>Navegação</h4>
                    <div class="footer__links">
                        <a href="index.php">Início</a>
                        <a href="produtos.php">Produtos</a>
                        <a href="sobre.php">Empresa</a>
                        <a href="contato.php">Contato</a>
                        <a href="orcamento.php">Orçamento</a>
                    </div>
                </div>

                <div class="footer__col">
                    <h4>Categorias</h4>
                    <div class="footer__links">
                        <a href="produtos.php?categoria=cimento">Cimentos</a>
                        <a href="produtos.php?categoria=massas">Massas e Argamassas</a>
                        <a href="produtos.php?categoria=tintas">Tintas</a>
                        <a href="produtos.php?categoria=ferramentas">Ferramentas</a>
                    </div>
                </div>

                <div class="footer__col">
                    <h4>Atendimento</h4>
                    <div class="footer__links">
                        <a href="tel:<?= e(APP_PHONE) ?>"><i class="bi bi-telephone"></i> <?= e(APP_PHONE) ?></a>
                        <a href="mailto:<?= e(APP_EMAIL) ?>"><i class="bi bi-envelope"></i> <?= e(APP_EMAIL) ?></a>
                        <a href="#"><i class="bi bi-geo-alt"></i> <?= e(APP_ADDRESS) ?></a>
                    </div>
                </div>
            </div>

            <div class="footer__bottom">
                <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Projeto acadêmico em PHP.</p>
                <p><a href="admin.php" class="footer__admin"><i class="bi bi-shield-lock"></i> Painel</a> · <?= e(APP_TAGLINE) ?></p>
            </div>
        </div>
    </footer>

    <a class="wa-float" href="<?= e(whatsapp_link('Ola! Quero um orcamento da Construcoes Rorato.')) ?>"
       aria-label="Falar no WhatsApp" title="Falar no WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Bootstrap 5 (JS bundle: ativa carousel, modal e accordion) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
