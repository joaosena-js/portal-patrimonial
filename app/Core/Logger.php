<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Registro de eventos em arquivo diário (storage/logs/app-AAAA-MM-DD.log).
 * Nunca envie senhas, tokens ou dados pessoais desnecessários no contexto.
 */
final class Logger
{
    public static function info(string $mensagem, array $contexto = []): void
    {
        self::escrever('INFO', $mensagem, $contexto);
    }

    public static function warning(string $mensagem, array $contexto = []): void
    {
        self::escrever('WARNING', $mensagem, $contexto);
    }

    public static function error(string $mensagem, array $contexto = []): void
    {
        self::escrever('ERROR', $mensagem, $contexto);
    }

    private static function escrever(string $nivel, string $mensagem, array $contexto): void
    {
        $pasta = BASE_PATH . '/storage/logs';

        if (!is_dir($pasta) && !@mkdir($pasta, 0750, true) && !is_dir($pasta)) {
            return;
        }

        $detalhes = $contexto === []
            ? ''
            : ' ' . json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);

        $linha = sprintf("[%s] %s: %s%s\n", date('Y-m-d H:i:s'), $nivel, $mensagem, $detalhes);

        @file_put_contents($pasta . '/app-' . date('Y-m-d') . '.log', $linha, FILE_APPEND | LOCK_EX);
    }
}
