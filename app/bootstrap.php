<?php

declare(strict_types=1);

/**
 * Inicialização comum à aplicação web e aos scripts de linha de comando:
 * constantes, autoload PSR-4, variáveis de ambiente, configuração e funções de apoio.
 */

use App\Core\Config;
use App\Core\Env;

define('BASE_PATH', dirname(__DIR__));

// Autoload PSR-4 simples (namespace App\ => pasta app/). Dispensa o Composer na instalação.
spl_autoload_register(static function (string $classe): void {
    $prefixo = 'App\\';

    if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) {
        return;
    }

    $arquivo = BASE_PATH . '/app/' . str_replace('\\', '/', substr($classe, strlen($prefixo))) . '.php';

    if (is_file($arquivo)) {
        require $arquivo;
    }
});

require_once BASE_PATH . '/app/Helpers/view.php';

Env::carregar(BASE_PATH . '/.env');
Config::carregar(require BASE_PATH . '/config/config.php');

date_default_timezone_set((string) Config::obter('timezone', 'America/Manaus'));
mb_internal_encoding('UTF-8');

// Erros nunca são exibidos ao usuário. São registrados em storage/logs.
$pastaLogs = BASE_PATH . '/storage/logs';

if (!is_dir($pastaLogs)) {
    @mkdir($pastaLogs, 0750, true);
}

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $pastaLogs . '/php-errors.log');
