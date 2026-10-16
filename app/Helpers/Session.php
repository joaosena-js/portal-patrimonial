<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Config;

/** Controle de sessão com cookie protegido e expiração por inatividade. */
final class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_name('portal_patrimonial_sid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        $limite = ((int) Config::obter('sessao_minutos', 30)) * 60;
        $agora = time();

        if (isset($_SESSION['_atividade']) && ($agora - (int) $_SESSION['_atividade']) > $limite) {
            self::destruir();
            FlashMessage::aviso('Sua sessão expirou por inatividade. Faça login novamente.');
        }

        $_SESSION['_atividade'] = $agora;
    }

    public static function obter(string $chave, mixed $padrao = null): mixed
    {
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function definir(string $chave, mixed $valor): void
    {
        $_SESSION[$chave] = $valor;
    }

    public static function remover(string $chave): void
    {
        unset($_SESSION[$chave]);
    }

    /** Troca o identificador da sessão (use ao autenticar para evitar fixação de sessão). */
    public static function regenerar(): void
    {
        session_regenerate_id(true);
    }

    /** Encerra a sessão atual e inicia uma nova, vazia. */
    public static function destruir(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
}
