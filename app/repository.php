<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/* ── Leitura do catálogo ─────────────────────────── */
function catalogo_array(): array
{
    return require __DIR__ . '/data/catalogo.php';
}

function repo_categorias(): array
{
    $pdo = db();

    if ($pdo === null) {
        return catalogo_array()['categorias'];
    }

    $linhas = $pdo->query(
        'SELECT slug, nome, descricao, icone FROM categorias ORDER BY id_categoria'
    )->fetchAll();

    $categorias = [];
    foreach ($linhas as $linha) {
        $categorias[$linha['slug']] = [
            'nome'      => $linha['nome'],
            'descricao' => (string) $linha['descricao'],
            'icone'     => (string) $linha['icone'],
        ];
    }

    return $categorias;
}

function repo_produtos(): array
{
    $pdo = db();

    if ($pdo === null) {
        return catalogo_array()['produtos'];
    }

    $sql = 'SELECT p.id_produto, p.nome, p.descricao, p.unidade,
                   p.preco_base, p.estoque, p.destaque, c.slug AS categoria
            FROM produtos p
            INNER JOIN categorias c ON c.id_categoria = p.id_categoria
            WHERE p.ativo = 1
            ORDER BY p.id_produto';

    $linhas = $pdo->query($sql)->fetchAll();

    return array_map(static function (array $l): array {
        return [
            'id'        => (int) $l['id_produto'],
            'nome'      => $l['nome'],
            'categoria' => $l['categoria'],
            'descricao' => (string) $l['descricao'],
            'unidade'   => $l['unidade'],
            'preco'     => $l['preco_base'] !== null ? (float) $l['preco_base'] : null,
            'destaque'  => (bool) $l['destaque'],
            'estoque'   => (string) $l['estoque'],
        ];
    }, $linhas);
}

function repo_filtrar_produtos(array $produtos, string $categoria = 'todos', string $busca = ''): array
{
    $termo = strtolower(trim($busca));

    $filtrados = array_filter($produtos, static function (array $produto) use ($categoria, $termo): bool {
        $mesmaCategoria = $categoria === 'todos' || $produto['categoria'] === $categoria;

        if (!$mesmaCategoria) {
            return false;
        }

        if ($termo === '') {
            return true;
        }

        $texto = strtolower($produto['nome'] . ' ' . $produto['descricao']);
        return strpos($texto, $termo) !== false;
    });

    return array_values($filtrados);
}

function repo_produto_por_id(array $produtos, int $id): ?array
{
    foreach ($produtos as $produto) {
        if ((int) $produto['id'] === $id) {
            return $produto;
        }
    }

    return null;
}

/* ── Formatação ──────────────────────────────────── */
function formatar_preco(?float $preco): string
{
    if ($preco === null || $preco <= 0) {
        return 'Sob consulta';
    }

    return 'R$ ' . number_format($preco, 2, ',', '.');
}

function formatar_data(?string $datetime): string
{
    if ($datetime === null || $datetime === '') {
        return '—';
    }

    $ts = strtotime($datetime);
    return $ts !== false ? date('d/m/Y H:i', $ts) : $datetime;
}

/* ── Gravação (orçamento N:N e contato) ──────────── */
function repo_salvar_orcamento(array $dados): array
{
    $pdo = db();

    if ($pdo === null) {
        return ['ok' => false, 'id' => null, 'erro' => 'Banco indisponível.'];
    }

    try {
        $pdo->beginTransaction();

        $stmtCliente = $pdo->prepare(
            'INSERT INTO clientes (nome, telefone, email) VALUES (:nome, :telefone, :email)'
        );
        $stmtCliente->execute([
            ':nome'     => $dados['nome'],
            ':telefone' => $dados['telefone'],
            ':email'    => $dados['email'] !== '' ? $dados['email'] : null,
        ]);
        $idCliente = (int) $pdo->lastInsertId();

        $stmtOrc = $pdo->prepare(
            'INSERT INTO orcamentos (id_cliente, observacoes) VALUES (:id_cliente, :obs)'
        );
        $stmtOrc->execute([
            ':id_cliente' => $idCliente,
            ':obs'        => $dados['observacoes'] !== '' ? $dados['observacoes'] : null,
        ]);
        $idOrcamento = (int) $pdo->lastInsertId();

        if ($dados['id_produto'] > 0) {
            $stmtItem = $pdo->prepare(
                'INSERT INTO itens_orcamento (id_orcamento, id_produto, quantidade, unidade)
                 VALUES (:id_orcamento, :id_produto, :quantidade, :unidade)'
            );
            $stmtItem->execute([
                ':id_orcamento' => $idOrcamento,
                ':id_produto'   => $dados['id_produto'],
                ':quantidade'   => (float) preg_replace('/[^0-9.,]/', '', $dados['quantidade']) ?: 1,
                ':unidade'      => $dados['unidade'] !== '' ? $dados['unidade'] : 'un',
            ]);
        }

        $pdo->commit();

        return ['ok' => true, 'id' => $idOrcamento, 'erro' => null];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Erro ao salvar orçamento: ' . $e->getMessage());
        return ['ok' => false, 'id' => null, 'erro' => 'Não foi possível salvar o orçamento.'];
    }
}

function repo_salvar_mensagem(array $dados): bool
{
    $pdo = db();

    if ($pdo === null) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO mensagens_contato (nome, contato, assunto, mensagem)
             VALUES (:nome, :contato, :assunto, :mensagem)'
        );

        return $stmt->execute([
            ':nome'     => $dados['nome'],
            ':contato'  => $dados['contato'],
            ':assunto'  => $dados['assunto'],
            ':mensagem' => $dados['mensagem'],
        ]);
    } catch (PDOException $e) {
        error_log('Erro ao salvar mensagem: ' . $e->getMessage());
        return false;
    }
}

/* ── Painel: listagens ───────────────────────────── */
function repo_listar_orcamentos(): array
{
    $pdo = db();

    if ($pdo === null) {
        return [];
    }

    $orcamentos = $pdo->query(
        'SELECT o.id_orcamento, o.status, o.observacoes, o.criado_em,
                c.nome, c.telefone, c.email, c.cidade
         FROM orcamentos o
         INNER JOIN clientes c ON c.id_cliente = o.id_cliente
         ORDER BY o.id_orcamento DESC'
    )->fetchAll();

    if ($orcamentos === []) {
        return [];
    }

    $itens = $pdo->query(
        'SELECT i.id_orcamento, i.quantidade, i.unidade, p.nome AS produto
         FROM itens_orcamento i
         INNER JOIN produtos p ON p.id_produto = i.id_produto
         ORDER BY i.id_item'
    )->fetchAll();

    $itensPorOrcamento = [];
    foreach ($itens as $item) {
        $itensPorOrcamento[(int) $item['id_orcamento']][] = $item;
    }

    foreach ($orcamentos as &$orcamento) {
        $orcamento['itens'] = $itensPorOrcamento[(int) $orcamento['id_orcamento']] ?? [];
    }
    unset($orcamento);

    return $orcamentos;
}

function repo_listar_mensagens(): array
{
    $pdo = db();

    if ($pdo === null) {
        return [];
    }

    return $pdo->query(
        'SELECT id_mensagem, nome, contato, assunto, mensagem, enviado_em
         FROM mensagens_contato
         ORDER BY id_mensagem DESC'
    )->fetchAll();
}

/* ── Painel: CRUD de produtos ────────────────────── */
function repo_categorias_lista(): array
{
    $pdo = db();
    if ($pdo === null) {
        return [];
    }

    return $pdo->query('SELECT id_categoria, nome, slug FROM categorias ORDER BY nome')->fetchAll();
}

function repo_listar_produtos_admin(): array
{
    $pdo = db();
    if ($pdo === null) {
        return [];
    }

    return $pdo->query(
        'SELECT p.id_produto, p.id_categoria, p.nome, p.descricao, p.unidade,
                p.preco_base, p.estoque, p.destaque, p.ativo, c.nome AS categoria_nome
         FROM produtos p
         INNER JOIN categorias c ON c.id_categoria = p.id_categoria
         ORDER BY p.id_produto DESC'
    )->fetchAll();
}

function repo_criar_produto(array $d): bool
{
    $pdo = db();
    if ($pdo === null) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO produtos (id_categoria, nome, descricao, unidade, preco_base, estoque, destaque, ativo)
             VALUES (:cat, :nome, :descricao, :unidade, :preco, :estoque, :destaque, :ativo)'
        );
        return $stmt->execute([
            ':cat'      => (int) $d['id_categoria'],
            ':nome'     => (string) $d['nome'],
            ':descricao'=> (string) $d['descricao'],
            ':unidade'  => (string) $d['unidade'],
            ':preco'    => $d['preco_base'],
            ':estoque'  => (string) $d['estoque'],
            ':destaque' => (int) $d['destaque'],
            ':ativo'    => (int) $d['ativo'],
        ]);
    } catch (PDOException $e) {
        error_log('Erro ao criar produto: ' . $e->getMessage());
        return false;
    }
}

function repo_atualizar_produto(int $id, array $d): bool
{
    $pdo = db();
    if ($pdo === null) {
        return false;
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE produtos
             SET id_categoria = :cat, nome = :nome, descricao = :descricao, unidade = :unidade,
                 preco_base = :preco, estoque = :estoque, destaque = :destaque, ativo = :ativo
             WHERE id_produto = :id'
        );
        return $stmt->execute([
            ':cat'      => (int) $d['id_categoria'],
            ':nome'     => (string) $d['nome'],
            ':descricao'=> (string) $d['descricao'],
            ':unidade'  => (string) $d['unidade'],
            ':preco'    => $d['preco_base'],
            ':estoque'  => (string) $d['estoque'],
            ':destaque' => (int) $d['destaque'],
            ':ativo'    => (int) $d['ativo'],
            ':id'       => $id,
        ]);
    } catch (PDOException $e) {
        error_log('Erro ao atualizar produto: ' . $e->getMessage());
        return false;
    }
}

function repo_excluir_produto(int $id): array
{
    $pdo = db();
    if ($pdo === null) {
        return ['ok' => false, 'erro' => 'Banco indisponível.'];
    }

    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM itens_orcamento WHERE id_produto = :id');
        $stmt->execute([':id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return ['ok' => false, 'erro' => 'Este produto está em orçamentos. Desative-o em vez de excluir.'];
        }

        $del = $pdo->prepare('DELETE FROM produtos WHERE id_produto = :id');
        $del->execute([':id' => $id]);

        return ['ok' => true, 'erro' => null];
    } catch (PDOException $e) {
        error_log('Erro ao excluir produto: ' . $e->getMessage());
        return ['ok' => false, 'erro' => 'Não foi possível excluir o produto.'];
    }
}
