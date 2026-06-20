<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/*
 * ============================================================
 *  Camada de repositório
 *  - Lê dados do banco (PDO) e os entrega como ARRAYS.
 *  - Caso o banco esteja offline, cai para o catálogo em
 *    array (app/data/catalogo.php) — fallback resiliente.
 *  - Todas as funções recebem parâmetros e usam return
 *    (sem variáveis globais), atendendo ao Tech Forge.
 * ============================================================
 */

/**
 * Carrega o catálogo em array (fonte de fallback / seed).
 *
 * @return array{categorias: array, produtos: array}
 */
function catalogo_array(): array
{
    return require __DIR__ . '/data/catalogo.php';
}

/**
 * Retorna as categorias indexadas por slug.
 *
 * @return array<string, array{nome:string, descricao:string, icone:string}>
 */
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

/**
 * Retorna a lista de produtos ativos no formato usado pelos templates.
 *
 * @return array<int, array<string, mixed>>
 */
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

    // Mapeia para o mesmo formato do catálogo em array.
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

/**
 * Filtra produtos por categoria (slug) e por um termo de busca.
 * Demonstra busca/filtro em array + validação com condicionais.
 *
 * @param array<int, array<string, mixed>> $produtos
 * @return array<int, array<string, mixed>>
 */
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

/**
 * Busca um produto pelo id dentro de uma lista (array) de produtos.
 *
 * @param array<int, array<string, mixed>> $produtos
 * @return array<string, mixed>|null
 */
function repo_produto_por_id(array $produtos, int $id): ?array
{
    foreach ($produtos as $produto) {
        if ((int) $produto['id'] === $id) {
            return $produto;
        }
    }

    return null;
}

/**
 * Formata um preço (float) como moeda brasileira, ou rótulo padrão.
 */
function formatar_preco(?float $preco): string
{
    if ($preco === null || $preco <= 0) {
        return 'Sob consulta';
    }

    return 'R$ ' . number_format($preco, 2, ',', '.');
}

/**
 * Salva um orçamento completo: cliente + orçamento + itens (N:N).
 * Usa transação para manter a integridade entre as tabelas.
 *
 * @param array{nome:string, telefone:string, email:string, id_produto:int, quantidade:string, unidade:string, observacoes:string} $dados
 * @return array{ok:bool, id:int|null, erro:string|null}
 */
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

        // Só grava o item (N:N) se um produto válido foi escolhido.
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

/**
 * Formata uma data/hora do banco (Y-m-d H:i:s) para o padrão brasileiro.
 */
function formatar_data(?string $datetime): string
{
    if ($datetime === null || $datetime === '') {
        return '—';
    }

    $ts = strtotime($datetime);
    return $ts !== false ? date('d/m/Y H:i', $ts) : $datetime;
}

/**
 * Lista todos os orçamentos (mais recentes primeiro), já com os dados do
 * cliente e a lista de itens (produtos) de cada orçamento.
 *
 * @return array<int, array<string, mixed>>
 */
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

    // Busca todos os itens de uma vez e agrupa por orçamento.
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

/**
 * Lista as mensagens de contato (mais recentes primeiro).
 *
 * @return array<int, array<string, mixed>>
 */
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

/*
 * ============================================================
 *  CRUD de produtos (painel administrativo)
 * ============================================================
 */

/**
 * Lista as categorias com id e nome (para o select do formulário).
 *
 * @return array<int, array{id_categoria:int, nome:string, slug:string}>
 */
function repo_categorias_lista(): array
{
    $pdo = db();
    if ($pdo === null) {
        return [];
    }

    return $pdo->query('SELECT id_categoria, nome, slug FROM categorias ORDER BY nome')->fetchAll();
}

/**
 * Lista TODOS os produtos (inclusive inativos) com o nome da categoria — para o admin.
 *
 * @return array<int, array<string, mixed>>
 */
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

/**
 * Cria um novo produto.
 *
 * @param array<string, mixed> $d
 */
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

/**
 * Atualiza um produto existente.
 *
 * @param array<string, mixed> $d
 */
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

/**
 * Exclui um produto. Se ele estiver em algum orçamento (FK), não exclui —
 * orienta a desativar, preservando o histórico.
 *
 * @return array{ok:bool, erro:string|null}
 */
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

/**
 * Salva uma mensagem de contato no banco.
 *
 * @param array{nome:string, contato:string, assunto:string, mensagem:string} $dados
 */
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
