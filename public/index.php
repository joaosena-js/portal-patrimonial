<?php

declare(strict_types=1);

use App\Core\ErrorHandler;
use App\Core\Router;
use App\Helpers\Session;

require dirname(__DIR__) . '/app/bootstrap.php';

/*
 * Descobre o prefixo da URL (ex.: /portal-patrimonial/public ou /portal-patrimonial no XAMPP)
 * e o caminho da requisição sem esse prefixo. Assim as rotas funcionam em qualquer pasta.
 */
$uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$publico = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$raiz = $publico === '' ? '' : rtrim(dirname($publico), '/');

$base = '';

foreach ([$publico, $raiz] as $candidato) {
    if ($candidato !== '' && ($uri === $candidato || str_starts_with($uri, $candidato . '/'))) {
        $base = $candidato;
        break;
    }
}

define('BASE_URL', $base);
define('CAMINHO_ATUAL', '/' . trim(substr($uri, strlen($base)), '/'));

ErrorHandler::registrar();

// Cabeçalhos de segurança.
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header(
    "Content-Security-Policy: default-src 'self'; "
    . "style-src 'self' https://cdn.jsdelivr.net; "
    . "script-src 'self' https://cdn.jsdelivr.net; "
    . "img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net; "
    . "frame-src https://www.youtube-nocookie.com; media-src 'self'; "
    . "frame-ancestors 'none'; form-action 'self'; base-uri 'self'"
);

if ($https) {
    header('Strict-Transport-Security: max-age=31536000');
}

Session::iniciar();

$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$metodo = $metodo === 'HEAD' ? 'GET' : $metodo;

(new Router(require BASE_PATH . '/routes/web.php'))->despachar($metodo, CAMINHO_ATUAL);
