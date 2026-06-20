# ✅ Mapa de Entrega — Rubrica × O que foi feito

Projeto **Construções Rorato** — site em PHP para empresa de materiais de construção,
reforma e acabamento. Cada critério da rubrica abaixo está marcado com **o que foi
implementado** e **onde encontrar** no projeto.

---

## 🧮 Modelagem e Banco de Dados

| Critério (pontos) | O que foi feito | Onde |
| --- | --- | --- |
| **Diagrama de Entidade-Relacionamento — DER** *(1.0)* | DER com as 6 entidades e seus relacionamentos, em PDF e no formato-fonte Mermaid. | `docs/DER_Rorato.pdf` · `docs/DER_Rorato.mmd` |
| **Tabelas suficientes — mínimo 3** *(0.5)* | Banco com **6 tabelas**: `categorias`, `produtos`, `clientes`, `orcamentos`, `itens_orcamento`, `mensagens_contato`. | `database/schema.sql` |
| **Chaves primárias** *(0.5)* | Toda tabela tem `PRIMARY KEY` `AUTO_INCREMENT` (`id_categoria`, `id_produto`, `id_cliente`, `id_orcamento`, `id_item`, `id_mensagem`). | `database/schema.sql` |
| **Pelo menos 1 chave estrangeira** *(0.5)* | **5 chaves estrangeiras**: `produtos→categorias`, `orcamentos→clientes`, `itens_orcamento→orcamentos`, `itens_orcamento→produtos`, `mensagens_contato→clientes`. | `database/schema.sql` (cláusulas `CONSTRAINT ... FOREIGN KEY`) |
| **Relacionamento muitos-para-muitos (N:N)** *(0.5)* | A tabela **`itens_orcamento`** liga **orçamentos ⇄ produtos** (um orçamento tem vários produtos; um produto aparece em vários orçamentos), com FK para as duas tabelas. | `database/schema.sql` (tabela `itens_orcamento`) |

---

## 🌐 Sistemas Operacionais e Redes

| Critério (pontos) | O que foi feito | Onde |
| --- | --- | --- |
| **Proibir listagem de diretórios** *(0.6)* | `Options -Indexes` na raiz e em `public/`; `app/` e `database/` ainda bloqueiam acesso direto com `Require all denied`. | `.htaccess`, `public/.htaccess`, `app/.htaccess`, `database/.htaccess` |
| **Aplicação na porta 8080** *(0.6)* | Script que sobe a aplicação com `php -S 0.0.0.0:8080 -t public`. | `scripts/iniciar-servidor.ps1` |
| **DNS local configurado** *(0.6)* | Script que adiciona `rorato.local` ao `hosts`; há evidência em imagem. | `scripts/configurar-hosts.ps1` · `docs/Evidencia_DNS_Local_hosts.png` · `docs/hosts-rorato.txt` |
| **Banco de dados com IP fixo** *(0.6)* | Conexão usa **IP fixo** `127.0.0.1` (e não `localhost`); basta trocar o IP para apontar a outro servidor. | `app/config.php` → `const DB_HOST = '127.0.0.1';` |
| **Aplicação e banco em máquinas separadas** *(0.6)* | **Não incluído** (combinado). A arquitetura já suporta: mude `DB_HOST` para o IP do servidor de banco. | — |

---

## 💻 Desenvolvimento Web Moderna

| Critério (pontos) | O que foi feito | Onde |
| --- | --- | --- |
| **Layout agradável e dinâmico com PHP** *(0.5)* | Design system próprio (tema claro/escuro, tipografia Sora/Inter, animações on-scroll), conteúdo gerado por PHP a partir do banco. | `public/*.php` · `public/assets/css/style.css` · `public/assets/js/app.js` |
| **Uso de template com PHP** *(0.5)* | Cabeçalho, rodapé e card de produto reaproveitados via `require` em todas as páginas. | `app/templates/header.php` · `footer.php` · `product-card.php` |
| **Bootstrap — mínimo 3 componentes** *(0.5)* | **6 componentes** usados: **Carousel** (home), **Card** (produtos), **Modal** (home), **Accordion** (contato/FAQ), **Breadcrumb** (páginas internas), **Alert** (formulários) + **Badge** (estoque). | `index.php`, `produtos.php`, `contato.php`, `orcamento.php`, `product-card.php` |
| **Conexão com banco de dados** *(0.5)* | Conexão **PDO** (singleton, prepared statements, transações) com fallback resiliente. | `app/database.php` |
| **Dados recuperados e exibidos na tela** *(0.5)* | Categorias e produtos lidos do MySQL e exibidos na home e no catálogo; preços vêm do banco. | `public/index.php`, `public/produtos.php` (via `repo_categorias()`, `repo_produtos()`) |
| **Uso correto de comandos PHP (if, while, foreach...)** *(0.5)* | `foreach` nas listagens de categorias/produtos; `if/else` nas validações e na lógica das páginas. | `public/index.php`, `produtos.php`, `orcamento.php`, `contato.php` |

---

## ⚙️ Tech Forge

| Critério (pontos) | O que foi feito | Onde |
| --- | --- | --- |
| **Arrays para estruturar dados** *(0.6)* | Catálogo em array (seed/fallback) e resultados do banco mapeados como arrays de produtos/categorias. | `app/data/catalogo.php` · `app/repository.php` (`repo_produtos()`) |
| **Modularização com funções** *(0.6)* | Funções dedicadas para conexão, leitura, filtro, formatação e gravação. | `app/repository.php`, `app/database.php`, `app/config.php` |
| **Parâmetros e retorno (sem variáveis globais)** *(0.6)* | Ex.: `repo_filtrar_produtos($produtos, $categoria, $busca)`, `repo_produto_por_id($produtos, $id)`, `repo_salvar_orcamento($dados)` — todas recebem parâmetros e usam `return`. | `app/repository.php` |
| **Busca/filtro em array** *(0.6)* | `repo_filtrar_produtos()` filtra por categoria e por termo de busca usando `array_filter`. | `app/repository.php` (usada em `public/produtos.php`) |
| **Validação com condicionais (if/else)** *(0.6)* | Campos obrigatórios e e-mail validados antes de gravar; estoque vira badge conforme condição. | `public/orcamento.php`, `public/contato.php`, `app/templates/product-card.php` |
