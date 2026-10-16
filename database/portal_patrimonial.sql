-- Central de Conhecimento Patrimonial - estrutura completa (MySQL 5.7+ / MariaDB 10.3+)
-- Para instalar do zero: DROP DATABASE portal_patrimonial; (se existir) e execute este arquivo.
-- Depois: dados_teste.sql (opcional) e php database/criar_admin.php

CREATE DATABASE IF NOT EXISTS portal_patrimonial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_patrimonial;

CREATE TABLE orgaos_unidades (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sigla     VARCHAR(20)  NOT NULL,
    nome      VARCHAR(190) NOT NULL,
    situacao  ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orgaos_sigla (sigla)
) ENGINE = InnoDB;

CREATE TABLE usuarios (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome              VARCHAR(150) NOT NULL,
    usuario           VARCHAR(60)  NOT NULL COMMENT 'Login no formato nome.sobrenome',
    email             VARCHAR(190) NOT NULL,
    senha             VARCHAR(255) NOT NULL COMMENT 'Hash gerado por password_hash()',
    perfil            ENUM('admin', 'gestor') NOT NULL,
    orgao_id          INT UNSIGNED NULL,
    setor             VARCHAR(120) NULL,
    cargo             VARCHAR(120) NULL,
    matricula         VARCHAR(30)  NULL,
    telefone          VARCHAR(20)  NULL,
    cpf_hash          CHAR(64)     NULL COMMENT 'HMAC-SHA256 do CPF. O CPF em si nunca é gravado',
    situacao          ENUM('pendente', 'ativo', 'inativo', 'rejeitado') NOT NULL DEFAULT 'pendente',
    deve_trocar_senha TINYINT(1) NOT NULL DEFAULT 0,
    tentativas_login  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_ate     DATETIME NULL,
    ultimo_acesso     DATETIME NULL,
    aprovado_em       DATETIME NULL,
    aprovado_por      INT UNSIGNED NULL,
    criado_em         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_usuario (usuario),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_cpf (cpf_hash),
    KEY idx_usuarios_orgao (orgao_id),
    KEY idx_usuarios_situacao (situacao),
    CONSTRAINT fk_usuarios_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos_unidades (id)
) ENGINE = InnoDB;

-- Arquivos enviados (documentos e vídeos). Ficam em storage/uploads, fora da pasta pública.
CREATE TABLE arquivos (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome_original VARCHAR(190) NOT NULL,
    caminho       VARCHAR(255) NOT NULL COMMENT 'Relativo a storage/uploads',
    extensao      VARCHAR(10)  NOT NULL,
    tamanho       BIGINT UNSIGNED NOT NULL,
    enviado_por   INT UNSIGNED NULL,
    criado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_arquivos_usuario FOREIGN KEY (enviado_por) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE trilhas (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo        VARCHAR(150) NOT NULL,
    descricao     VARCHAR(1000) NULL,
    area          ENUM('geral', 'bens_moveis', 'bens_imoveis') NOT NULL DEFAULT 'geral',
    situacao      ENUM('rascunho', 'publicada', 'arquivada') NOT NULL DEFAULT 'rascunho',
    criado_por    INT UNSIGNED NULL,
    criado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_trilhas_situacao (situacao),
    CONSTRAINT fk_trilhas_criador FOREIGN KEY (criado_por) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE modulos (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    trilha_id         INT UNSIGNED NOT NULL,
    titulo            VARCHAR(150) NOT NULL,
    descricao         VARCHAR(500) NULL,
    tipo              ENUM('texto', 'video', 'documento', 'link') NOT NULL DEFAULT 'texto',
    conteudo_url      VARCHAR(500) NULL,
    conteudo_texto    MEDIUMTEXT NULL,
    ordem             SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    carga_horaria_min SMALLINT UNSIGNED NULL,
    ativo             TINYINT(1) NOT NULL DEFAULT 1,
    criado_em         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_modulos_trilha_ordem (trilha_id, ordem),
    CONSTRAINT fk_modulos_trilha FOREIGN KEY (trilha_id) REFERENCES trilhas (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE inscricoes (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id   INT UNSIGNED NOT NULL,
    trilha_id    INT UNSIGNED NOT NULL,
    situacao     ENUM('em_andamento', 'concluida') NOT NULL DEFAULT 'em_andamento',
    iniciada_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    concluida_em DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_inscricao (usuario_id, trilha_id),
    KEY idx_inscricoes_trilha (trilha_id),
    CONSTRAINT fk_inscricoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_inscricoes_trilha FOREIGN KEY (trilha_id) REFERENCES trilhas (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE progresso (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id   INT UNSIGNED NOT NULL,
    modulo_id    INT UNSIGNED NOT NULL,
    concluido_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_progresso (usuario_id, modulo_id),
    KEY idx_progresso_modulo (modulo_id),
    CONSTRAINT fk_progresso_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_progresso_modulo FOREIGN KEY (modulo_id) REFERENCES modulos (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE avisos (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo        VARCHAR(150) NOT NULL,
    mensagem      VARCHAR(2000) NOT NULL,
    urgente       TINYINT(1) NOT NULL DEFAULT 0,
    publicado_por INT UNSIGNED NULL,
    publicado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expira_em     DATE NULL,
    ativo         TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_avisos_ativo_data (ativo, publicado_em),
    CONSTRAINT fk_avisos_autor FOREIGN KEY (publicado_por) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- Vídeos operacionais: YouTube, link institucional ou arquivo enviado.
-- categoria e area_responsavel são texto (validado na aplicação) para permitir novas categorias sem ALTER TABLE.
CREATE TABLE videos (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo           VARCHAR(150) NOT NULL,
    descricao        VARCHAR(1000) NULL,
    categoria        VARCHAR(30) NOT NULL,
    area_responsavel VARCHAR(10) NOT NULL,
    origem           ENUM('youtube', 'link', 'arquivo') NOT NULL,
    url              VARCHAR(500) NULL,
    youtube_id       VARCHAR(20) NULL,
    arquivo_id       INT UNSIGNED NULL,
    publicado_em     DATE NOT NULL,
    autor_id         INT UNSIGNED NULL,
    ativo            TINYINT(1) NOT NULL DEFAULT 1,
    criado_em        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_videos_listagem (ativo, publicado_em),
    KEY idx_videos_categoria (categoria),
    CONSTRAINT fk_videos_arquivo FOREIGN KEY (arquivo_id) REFERENCES arquivos (id) ON DELETE SET NULL,
    CONSTRAINT fk_videos_autor FOREIGN KEY (autor_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- Materiais de apoio, organizados por grupo (ex.: "Transferência entre Órgãos") para exibição em accordion.
CREATE TABLE material_grupos (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo    VARCHAR(150) NOT NULL,
    descricao VARCHAR(500) NULL,
    ordem     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    ativo     TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_material_grupos_ordem (ativo, ordem)
) ENGINE = InnoDB;

CREATE TABLE materiais (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    grupo_id      INT UNSIGNED NOT NULL,
    titulo        VARCHAR(150) NOT NULL,
    descricao     VARCHAR(500) NULL,
    tipo          VARCHAR(20) NOT NULL COMMENT 'cartilha, manual, checklist, modelo, fluxograma, formulario',
    arquivo_id    INT UNSIGNED NOT NULL,
    publicado_em  DATE NOT NULL,
    autor_id      INT UNSIGNED NULL,
    ativo         TINYINT(1) NOT NULL DEFAULT 1,
    criado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_materiais_grupo (grupo_id, ativo),
    CONSTRAINT fk_materiais_grupo FOREIGN KEY (grupo_id) REFERENCES material_grupos (id),
    CONSTRAINT fk_materiais_arquivo FOREIGN KEY (arquivo_id) REFERENCES arquivos (id),
    CONSTRAINT fk_materiais_autor FOREIGN KEY (autor_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE legislacoes (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria       VARCHAR(30) NOT NULL,
    numero          VARCHAR(60) NULL,
    titulo          VARCHAR(190) NOT NULL,
    ementa          VARCHAR(1000) NULL COMMENT 'Do que se trata',
    data_publicacao DATE NULL,
    situacao        ENUM('vigente', 'revogada') NOT NULL DEFAULT 'vigente',
    url             VARCHAR(500) NULL,
    arquivo_id      INT UNSIGNED NULL,
    autor_id        INT UNSIGNED NULL,
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    criado_em       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_legislacoes_categoria (ativo, categoria),
    CONSTRAINT fk_legislacoes_arquivo FOREIGN KEY (arquivo_id) REFERENCES arquivos (id) ON DELETE SET NULL,
    CONSTRAINT fk_legislacoes_autor FOREIGN KEY (autor_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- Perguntas frequentes (Etapa 2). Os vínculos apontam para vídeos, materiais, legislações ou contatos.
CREATE TABLE faqs (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria     VARCHAR(30) NOT NULL,
    pergunta      VARCHAR(255) NOT NULL,
    resposta      TEXT NOT NULL,
    ordem         SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    ativo         TINYINT(1) NOT NULL DEFAULT 1,
    autor_id      INT UNSIGNED NULL,
    criado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_faqs_categoria (ativo, categoria, ordem),
    CONSTRAINT fk_faqs_autor FOREIGN KEY (autor_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE faq_vinculos (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    faq_id        INT UNSIGNED NOT NULL,
    tipo          VARCHAR(20) NOT NULL COMMENT 'video, material, legislacao, contato',
    referencia_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_faq_vinculo (faq_id, tipo, referencia_id),
    CONSTRAINT fk_faq_vinculos_faq FOREIGN KEY (faq_id) REFERENCES faqs (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE contatos (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    area          VARCHAR(10) NOT NULL COMMENT 'cpat, dbm, dbi',
    nome          VARCHAR(150) NOT NULL,
    email         VARCHAR(190) NULL,
    telefone      VARCHAR(60) NULL,
    endereco      VARCHAR(255) NULL,
    horario       VARCHAR(120) NULL,
    competencias  VARCHAR(1500) NULL,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_contatos_area (area)
) ENGINE = InnoDB;

-- Notificações: uma linha por novidade. A leitura é registrada por usuário em notificacoes_lidas
-- (evita criar uma linha para cada usuário a cada publicação).
CREATE TABLE notificacoes (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tipo        VARCHAR(20) NOT NULL COMMENT 'video, material, legislacao, faq, aviso',
    titulo      VARCHAR(190) NOT NULL,
    entidade_id INT UNSIGNED NULL,
    urgente     TINYINT(1) NOT NULL DEFAULT 0,
    criado_em   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notificacoes_criado (criado_em)
) ENGINE = InnoDB;

CREATE TABLE notificacoes_lidas (
    usuario_id     INT UNSIGNED NOT NULL,
    notificacao_id INT UNSIGNED NOT NULL,
    lida_em        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, notificacao_id),
    CONSTRAINT fk_lidas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_lidas_notificacao FOREIGN KEY (notificacao_id) REFERENCES notificacoes (id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE logs_atividade (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NULL,
    acao        VARCHAR(60)  NOT NULL,
    entidade    VARCHAR(60)  NULL,
    entidade_id INT UNSIGNED NULL,
    descricao   VARCHAR(255) NULL,
    ip          VARCHAR(45)  NULL,
    criado_em   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_logs_criado_em (criado_em),
    KEY idx_logs_usuario (usuario_id),
    KEY idx_logs_acao_ip (acao, ip, criado_em),
    CONSTRAINT fk_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- Áreas de contato (os dados são preenchidos pelo administrador na tela Contatos).
INSERT INTO contatos (area, nome) VALUES
    ('cpat', 'Coordenadoria de Patrimônio (CPAT)'),
    ('dbm', 'Departamento de Bens Móveis (DBM)'),
    ('dbi', 'Departamento de Bens Imóveis (DBI)');
