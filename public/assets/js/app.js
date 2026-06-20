/* ════════════════════════════════════════════════
   Construções Rorato — interações
════════════════════════════════════════════════ */

/* ── Header: sombra ao rolar ─────────────────────── */
const nav = document.getElementById('siteNav');
if (nav) {
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 12);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

/* ── Tema claro/escuro ───────────────────────────── */
const themeBtn = document.getElementById('themeToggle');
if (themeBtn) {
    const root = document.documentElement;
    const setIcon = (t) => {
        themeBtn.innerHTML = t === 'dark'
            ? '<i class="bi bi-sun"></i>'
            : '<i class="bi bi-moon-stars"></i>';
    };
    setIcon(root.getAttribute('data-theme') || 'light');
    themeBtn.addEventListener('click', () => {
        const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        root.setAttribute('data-bs-theme', next);
        try { localStorage.setItem('theme', next); } catch (e) {}
        setIcon(next);
    });
}

/* ── Menu mobile ─────────────────────────────────── */
const toggle = document.getElementById('navToggle');
const links  = document.getElementById('navLinks');
if (toggle && links) {
    toggle.addEventListener('click', () => {
        const open = links.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.innerHTML = open ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-list"></i>';
    });
    links.querySelectorAll('a').forEach((a) => {
        a.addEventListener('click', () => {
            links.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = '<i class="bi bi-list"></i>';
        });
    });
}

/* ── Filtro de categoria: envia ao mudar ─────────── */
document.querySelectorAll('select[name="categoria"]').forEach((select) => {
    select.addEventListener('change', () => {
        const form = select.closest('form');
        if (form) form.requestSubmit();
    });
});

/* ── Busca automática (debounce) — mantém o botão ── */
const searchInput = document.getElementById('q');
if (searchInput) {
    // Mantém o foco e o cursor no fim após o reload do filtro.
    if (searchInput.value) {
        const v = searchInput.value;
        searchInput.focus();
        searchInput.value = '';
        searchInput.value = v;
    }
    let timer;
    searchInput.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const form = searchInput.closest('form');
            if (form) form.requestSubmit();
        }, 550);
    });
}

/* ── Floating label para <select> ────────────────── */
document.querySelectorAll('.field select').forEach((select) => {
    const sync = () => select.classList.toggle('has-value', select.value !== '');
    select.addEventListener('change', sync);
    sync();
});

/* ── Reveal on scroll ────────────────────────────── */
if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
} else {
    document.querySelectorAll('.reveal').forEach((el) => el.classList.add('in'));
}

/* ── Progresso dos passos do formulário ──────────── */
const fsteps = document.querySelectorAll('.fstep');
if (fsteps.length) {
    const groups = [['nome', 'telefone'], ['produto', 'quantidade'], ['mensagem']];
    const filled = (name) => {
        const el = document.querySelector(`[name="${name}"]`);
        return el && el.value.trim() !== '';
    };
    const update = () => {
        let reached = 0;
        groups.forEach((fields, idx) => { if (fields.every(filled)) reached = idx + 1; });
        fsteps.forEach((step, idx) => {
            step.classList.toggle('done', idx < reached);
            step.classList.toggle('active', idx === reached);
        });
    };
    document.querySelectorAll('#quoteForm input, #quoteForm select, #quoteForm textarea')
        .forEach((el) => el.addEventListener('input', update));
    update();
}

/* ── Admin: modal de criar/editar produto ────────── */
const modalProduto = document.getElementById('modalProduto');
if (modalProduto) {
    const form  = document.getElementById('formProduto');
    const title = document.getElementById('modalProdutoLabel');
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
    const check = (id, on) => { const el = document.getElementById(id); if (el) el.checked = !!on; };
    const syncSelects = () => form.querySelectorAll('select').forEach((s) => s.classList.toggle('has-value', s.value !== ''));

    // Botão "Novo produto": limpa o formulário.
    const btnNovo = document.getElementById('btnNovoProduto');
    if (btnNovo) {
        btnNovo.addEventListener('click', () => {
            form.reset();
            set('prodAcao', 'criar');
            set('prodId', '');
            check('prodAtivo', true);
            check('prodDestaque', false);
            title.textContent = 'Novo produto';
            syncSelects();
        });
    }

    // Botões "Editar": preenchem o formulário com os dados da linha.
    document.querySelectorAll('.btn-edit').forEach((btn) => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            set('prodAcao', 'atualizar');
            set('prodId', d.id);
            set('prodNome', d.nome);
            set('prodCategoria', d.categoria);
            set('prodDescricao', d.descricao);
            set('prodUnidade', d.unidade);
            set('prodPreco', d.preco);
            set('prodEstoque', d.estoque);
            check('prodDestaque', d.destaque === '1');
            check('prodAtivo', d.ativo === '1');
            title.textContent = 'Editar produto';
            syncSelects();
        });
    });
}

/* ── Scroll suave para âncoras ───────────────────── */
document.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (e) => {
        const id = link.getAttribute('href');
        if (id.length < 2) return;
        const target = document.querySelector(id);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
