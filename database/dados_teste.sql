-- Dados FICTÍCIOS para demonstração do piloto. Não use em produção.
-- Execute depois de portal_patrimonial.sql. Não cria usuários: use criar_admin.php
-- e cadastre gestores de teste pelo autocadastro (/cadastro) ou pela tela de Usuários.
-- Não cria vídeos, materiais nem legislação: publique-os pelas telas administrativas.

USE portal_patrimonial;

INSERT INTO orgaos_unidades (sigla, nome) VALUES
    ('SEAD', 'Secretaria de Administração e Gestão'),
    ('SEC-A', 'Secretaria de Exemplo A'),
    ('SEC-B', 'Secretaria de Exemplo B'),
    ('FUN-C', 'Fundação de Exemplo C')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

INSERT INTO trilhas (id, titulo, descricao, area, situacao) VALUES
    (1, 'Fundamentos da gestão patrimonial',
        'Conteúdo introdutório para servidores designados como gestor patrimonial. Dados de demonstração.',
        'geral', 'publicada'),
    (2, 'Bens móveis: rotina do gestor patrimonial',
        'Trilha de demonstração sobre as rotinas ligadas aos bens móveis do órgão.',
        'bens_moveis', 'publicada'),
    (3, 'Bens imóveis: cadastro e acompanhamento',
        'Trilha em elaboração. Ainda não visível para os gestores.',
        'bens_imoveis', 'rascunho')
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

INSERT INTO modulos (trilha_id, titulo, descricao, tipo, conteudo_url, conteudo_texto, ordem, carga_horaria_min) VALUES
    (1, 'O papel do gestor patrimonial', 'Apresentação da função.', 'texto', NULL,
        'Conteúdo de demonstração.\n\nNesta etapa será apresentada a função do gestor patrimonial no órgão ou unidade e a relação com a CPAT, o DBM e o DBI.\n\nO texto definitivo deve ser elaborado pelos departamentos responsáveis.', 1, 15),
    (1, 'Como o portal funciona', 'Orientações de uso.', 'texto', NULL,
        'Conteúdo de demonstração.\n\nExplique aqui como acessar as trilhas, registrar o progresso e consultar o quadro de avisos.', 2, 10),
    (1, 'Site da Secretaria de Administração e Gestão', 'Link externo de exemplo.', 'link',
        'https://www.sead.am.gov.br', NULL, 3, 5),
    (2, 'Recebimento e incorporação de bens', 'Conteúdo de demonstração.', 'texto', NULL,
        'Conteúdo de demonstração.\n\nEste módulo tratará dos procedimentos de recebimento e incorporação, conforme as orientações da CPAT e do DBM.', 1, 20),
    (2, 'Movimentação e transferência', 'Conteúdo de demonstração.', 'texto', NULL,
        'Conteúdo de demonstração.\n\nEste módulo tratará da movimentação e da transferência de bens entre unidades.', 2, 20),
    (2, 'Inventário patrimonial', 'Conteúdo de demonstração.', 'texto', NULL,
        'Conteúdo de demonstração.\n\nEste módulo tratará do inventário e das responsabilidades do gestor.', 3, 25),
    (3, 'Conceitos básicos sobre bens imóveis', 'Em elaboração.', 'texto', NULL,
        'Conteúdo em elaboração pelo DBI.', 1, 15);

INSERT INTO avisos (titulo, mensagem, expira_em) VALUES
    ('Piloto do Portal de Capacitação Patrimonial',
     'Este portal está em fase piloto. Sugestões e dificuldades de uso devem ser enviadas à CPAT.', NULL),
    ('Atualização de contatos dos gestores',
     'Aviso de demonstração. Mantenha seus dados de contato atualizados junto à CPAT.', NULL);

-- Assuntos (grupos do accordion) da página de Materiais. Os arquivos são enviados pela tela administrativa.
INSERT INTO material_grupos (titulo, descricao, ordem) VALUES
    ('Transferência entre Órgãos', 'Checklist, modelos de ofício e despacho, fluxograma e manual do processo.', 1),
    ('Inventário Patrimonial', 'Cartilha, checklist e relatório modelo.', 2),
    ('Alienação e Baixa', 'Orientações e modelos para alienação e baixa de bens.', 3);

-- Aviso urgente de demonstração (aparece primeiro e com destaque).
INSERT INTO avisos (titulo, mensagem, urgente, expira_em) VALUES
    ('Aviso urgente de demonstração',
     'Exemplo de comunicado urgente. Em uso real, descreva aqui a providência e o prazo.', 1, NULL);

-- Perguntas frequentes de demonstração. Os textos definitivos devem ser elaborados pela CPAT, DBM e DBI.
INSERT INTO faqs (categoria, pergunta, resposta, ordem) VALUES
    ('transferencias', 'Como realizar transferência entre órgãos?',
     'Resposta de demonstração. Descreva aqui o passo a passo adotado pela CPAT e vincule o vídeo, o checklist e os modelos.', 1),
    ('inventario', 'Como realizar o inventário?',
     'Resposta de demonstração. Descreva aqui as etapas do inventário e vincule a cartilha e o checklist.', 1),
    ('baixa', 'Como efetuar baixa patrimonial?',
     'Resposta de demonstração. Descreva aqui o procedimento e vincule a legislação aplicável.', 1),
    ('apoio', 'Como solicitar apoio da CPAT?',
     'Resposta de demonstração. Informe os canais de atendimento e vincule a página de contatos.', 1),
    ('apoio', 'Como atualizar o gestor patrimonial do meu órgão?',
     'Resposta de demonstração. Explique o procedimento de designação e substituição do gestor.', 2);
