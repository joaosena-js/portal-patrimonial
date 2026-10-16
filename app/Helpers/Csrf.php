<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\HttpException;
use App\Core\Logger;

/** Proteção contra requisições forjadas (CSRF) com token por sessão. */
final class Csrf
{
    private const CHAVE = '_csrf_token';

    public static function token(): string
    {
        $token = Session::obter(self::CHAVE);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::definir(self::CHAVE, $token);
        }

        return $token;
    }

    public static function renovar(): void
    {
        Session::remover(self::CHAVE);
    }

    public static function validarRequisicao(): void
    {
        $enviado = $_POST['_csrf'] ?? '';

        if (!is_string($enviado) || !hash_equals(self::token(), $enviado)) {
            Logger::warning('Token CSRF inválido', [
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);

            throw new HttpException(419, 'Token CSRF inválido.');
        }
    }
}
