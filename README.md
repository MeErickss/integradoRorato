# Construções Rorato — Projeto PHP

Projeto acadêmico em PHP para o cliente real **Construções Rorato**, empresa de
materiais para construção, reforma e renovação (cimentos, massas, tintas e ferramentas).

O site é dinâmico, usa **templates em PHP**, **Bootstrap 5**, lê e grava dados em
um **banco MySQL** (com fallback automático para um catálogo em array caso o banco
esteja indisponível).

---

## Como rodar

### Opção A — Porta 8080 (recomendada para a rubrica)

```powershell
# 1. Importe o banco (apenas na primeira vez)
C:\xampp\mysql\bin\mysql.exe -u root < database/schema.sql

# 2. Inicie a aplicação na porta 8080
powershell -ExecutionPolicy Bypass -File scripts/iniciar-servidor.ps1
```

Acesse:

```text
http://localhost:8080
http://rorato.local:8080   (com DNS local — ver abaixo)
```

A pasta servida é `public/`. As pastas `app/` e `database/` ficam **fora** do
document root e também são bloqueadas por `.htaccess`.

### Opção B — XAMPP / Apache

1. Inicie **Apache** e **MySQL** no painel do XAMPP.
2. Importe `database/schema.sql` (via phpMyAdmin ou linha de comando).
3. Acesse `http://localhost/rorato/` (o `index.php` da raiz redireciona para `public/`).

> Para servir o Apache na porta 8080, adicione `Listen 8080` no `httpd.conf`
> ou crie um VirtualHost apontando para a pasta `public/`.

---

## DNS local

Mapeie o domínio `rorato.local` para o IP local rodando (como Administrador):

```powershell
powershell -ExecutionPolicy Bypass -File scripts/configurar-hosts.ps1
```

Isso adiciona ao `C:\Windows\System32\drivers\etc\hosts`:

```text
127.0.0.1    rorato.local
```

---

## Banco de dados (IP fixo)

As credenciais ficam em `app/config.php`. O host usa **IP fixo** (`127.0.0.1`),
e não `localhost`, atendendo ao requisito de rede:

```php
const DB_HOST = '127.0.0.1';   // IP fixo do servidor de banco
const DB_PORT = '3306';
const DB_NAME = 'rorato_db';
```

Para apontar o banco para outra máquina, basta trocar `DB_HOST` pelo IP fixo
do servidor MySQL.

---

## Estrutura

```text
app/
  config.php            Constantes do app + credenciais do banco (IP fixo)
  database.php          Conexão PDO (singleton) com fallback
  repository.php        Leitura/gravação no banco; filtros e validações em array
  data/catalogo.php     Catálogo em array (seed / fallback) — Tech Forge
  templates/            header.php, footer.php, product-card.php (templates PHP)
public/
  index.php             Home (hero, categorias, carousel Bootstrap, modal)
  produtos.php          Catálogo com filtro/busca + breadcrumb
  orcamento.php         Formulário que grava cliente + orçamento + item (N:N)
  contato.php           Mensagem de contato + accordion (FAQ)
  sobre.php             Página institucional
  assets/               css/style.css, js/app.js, img/
  .htaccess             Proíbe listagem de diretórios
database/
  schema.sql            Estrutura + dados (seed) do banco
docs/
  DER_Rorato.pdf/.mmd   Diagrama Entidade-Relacionamento
  ENTREGA_RUBRICA.md    Mapa de onde cada item da rubrica foi atendido
scripts/
  iniciar-servidor.ps1  Sobe a aplicação na porta 8080
  configurar-hosts.ps1  Configura o DNS local (rorato.local)
.htaccess               Proíbe listagem de diretórios (raiz)
```

---

## Tecnologias

- **PHP 8** (procedural, com funções modularizadas e templates)
- **MySQL** (PDO, prepared statements, transações)
- **Bootstrap 5** + Bootstrap Icons
- CSS custom (tema da marca) e JavaScript (animações, máscara de formulário)
