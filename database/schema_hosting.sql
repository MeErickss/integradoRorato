-- ============================================================
--  Construções Rorato — SQL para HOSPEDAGEM COMPARTILHADA
--  (ProFreeHost / InfinityFree / cPanel etc.)
--
--  Diferença para o schema.sql normal: NÃO tem "CREATE DATABASE"
--  nem "USE", porque no host o banco já existe e o nome é definido
--  por eles. No phpMyAdmin, SELECIONE o seu banco primeiro e só
--  então importe este arquivo.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS itens_orcamento;
DROP TABLE IF EXISTS mensagens_contato;
DROP TABLE IF EXISTS orcamentos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS categorias;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL UNIQUE,
    descricao VARCHAR(255) NULL,
    icone VARCHAR(40) NULL
);

CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descricao TEXT NULL,
    unidade VARCHAR(30) NOT NULL,
    preco_base DECIMAL(10, 2) NULL,
    estoque VARCHAR(40) NULL,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_produtos_categorias
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    email VARCHAR(120) NULL,
    cidade VARCHAR(80) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orcamentos (
    id_orcamento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'recebido',
    observacoes TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orcamentos_clientes
        FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

CREATE TABLE itens_orcamento (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_orcamento INT NOT NULL,
    id_produto INT NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL,
    unidade VARCHAR(30) NOT NULL,
    ambiente VARCHAR(80) NULL,
    CONSTRAINT fk_itens_orcamento_orcamentos
        FOREIGN KEY (id_orcamento) REFERENCES orcamentos(id_orcamento),
    CONSTRAINT fk_itens_orcamento_produtos
        FOREIGN KEY (id_produto) REFERENCES produtos(id_produto)
);

CREATE TABLE mensagens_contato (
    id_mensagem INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NULL,
    nome VARCHAR(120) NOT NULL,
    contato VARCHAR(120) NOT NULL,
    assunto VARCHAR(120) NOT NULL,
    mensagem TEXT NOT NULL,
    enviado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mensagens_clientes
        FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
);

-- ===== Dados iniciais =====
INSERT INTO categorias (nome, slug, descricao, icone) VALUES
('Cimentos',            'cimento',     'Sacos e compostos para estrutura, assentamento e pequenos reparos.', 'factory'),
('Massas e argamassas', 'massas',      'Produtos para preparar paredes, pisos, revestimentos e acabamentos.', 'layers'),
('Tintas',              'tintas',      'Tintas, seladores e complementos para áreas internas e externas.',    'paint'),
('Ferramentas',         'ferramentas', 'Itens de apoio para reforma, pintura, acabamento e manutenção.',      'toolbox');

INSERT INTO produtos (id_categoria, nome, descricao, unidade, preco_base, estoque, destaque, ativo) VALUES
((SELECT id_categoria FROM categorias WHERE slug='cimento'),     'Cimento CP II 50kg',              'Cimento versátil para concretos, argamassas e obras residenciais.',          'saco',     38.90,  'Pronta entrega', 1, 1),
((SELECT id_categoria FROM categorias WHERE slug='cimento'),     'Cimento CP V ARI 50kg',           'Alta resistência inicial para etapas que precisam de agilidade.',            'saco',     44.50,  'Consultar',      0, 1),
((SELECT id_categoria FROM categorias WHERE slug='massas'),      'Argamassa AC-I 20kg',             'Indicada para assentamento de cerâmicas em áreas internas.',                 'pacote',   24.90,  'Pronta entrega', 1, 1),
((SELECT id_categoria FROM categorias WHERE slug='massas'),      'Massa corrida PVA 25kg',          'Acabamento liso em paredes internas antes da pintura.',                      'barrica',  79.90,  'Pronta entrega', 1, 1),
((SELECT id_categoria FROM categorias WHERE slug='massas'),      'Massa acrílica 25kg',             'Nivelamento resistente para áreas internas e externas.',                     'barrica',  98.00,  'Consultar',      0, 1),
((SELECT id_categoria FROM categorias WHERE slug='tintas'),      'Tinta acrílica premium 18L',      'Acabamento fosco lavável com boa cobertura para fachadas e interiores.',     'lata',    289.90, 'Pronta entrega', 1, 1),
((SELECT id_categoria FROM categorias WHERE slug='tintas'),      'Selador acrílico 18L',            'Prepara superfícies novas e melhora o rendimento da tinta.',                 'lata',    159.90, 'Pronta entrega', 0, 1),
((SELECT id_categoria FROM categorias WHERE slug='ferramentas'), 'Rolo de pintura anti-respingo',   'Rolo para tinta acrílica, látex e acabamentos em paredes.',                  'unidade',  29.90,  'Pronta entrega', 1, 1),
((SELECT id_categoria FROM categorias WHERE slug='ferramentas'), 'Desempenadeira de aço',           'Ferramenta para aplicação de massa, argamassa e nivelamento.',               'unidade',  34.90,  'Consultar',      0, 1);
