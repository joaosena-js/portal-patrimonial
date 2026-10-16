<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Configurações gerais da aplicação.
 * Os valores sensíveis ou dependentes do ambiente vêm do arquivo .env.
 */
return [
    'nome' => Env::obter('APP_NOME', 'Portal de Capacitação Patrimonial'),
    'ambiente' => Env::obter('APP_ENV', 'production'),
    'debug' => Env::booleano('APP_DEBUG', false),
    'timezone' => 'America/Manaus',

    // Tempo de inatividade, em minutos, antes de encerrar a sessão.
    'sessao_minutos' => (int) Env::obter('SESSAO_MINUTOS', '30'),

    // Proteção contra tentativas repetidas de login.
    'login_max_tentativas' => 5,
    'login_bloqueio_minutos' => 15,

    // Registros por página nas listagens.
    'itens_por_pagina' => 15,

    // Chave secreta da aplicação (usada no hash do CPF). Gere uma única vez e não altere depois.
    'app_key' => (string) Env::obter('APP_KEY', ''),

    // Autocadastro: false libera o acesso logo após o cadastro; true deixa o cadastro "pendente"
    // até um administrador aprovar (o fluxo de aprovação continua disponível na tela de usuários).
    'cadastro_requer_aprovacao' => Env::booleano('CADASTRO_REQUER_APROVACAO', false),

    // Máximo de cadastros por hora vindos do mesmo IP (a rede do órgão pode ter um IP só para muitos servidores).
    'cadastro_max_por_hora_ip' => (int) Env::obter('CADASTRO_MAX_POR_HORA_IP', '20'),

    // Endereço público (apenas esquema e domínio), usado em links enviados por e-mail.
    'app_url' => rtrim((string) Env::obter('APP_URL', ''), '/'),

    // E-mail: "log" grava em storage/logs/emails.log; "mail" usa a função mail() do PHP.
    'mail_driver' => (string) Env::obter('MAIL_DRIVER', 'log'),
    'mail_from' => (string) Env::obter('MAIL_FROM', 'nao-responda@localhost'),

    // Limites de upload em MB (o php.ini também limita: upload_max_filesize e post_max_size).
    'upload_documento_mb' => (int) Env::obter('UPLOAD_DOCUMENTO_MB', '20'),
    'upload_video_mb' => (int) Env::obter('UPLOAD_VIDEO_MB', '200'),
];
