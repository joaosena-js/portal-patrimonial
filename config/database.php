<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Credenciais do banco de dados, lidas do arquivo .env.
 * Em produção, use um usuário próprio com permissão apenas sobre este banco.
 */
return [
    'host' => Env::obter('DB_HOST', 'localhost'),
    'port' => (int) Env::obter('DB_PORT', '3306'),
    'database' => Env::obter('DB_NAME', 'portal_patrimonial'),
    'username' => Env::obter('DB_USER', 'root'),
    'password' => Env::obter('DB_PASS', ''),
];
