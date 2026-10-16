<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Leitor simples do arquivo .env (CHAVE=valor, uma por linha).
 * Variáveis já definidas no ambiente do servidor têm prioridade sobre o arquivo.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $variaveis = [];

    public static function carregar(string $arquivo): void
    {
        if (!is_readable($arquivo)) {
            return;
        }

        $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($linhas as $linha) {
            $linha = trim($linha);

            if ($linha === '' || $linha[0] === '#' || !str_contains($linha, '=')) {
                continue;
            }

            [$chave, $valor] = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim($valor);

            $abre = $valor[0] ?? '';
            if (strlen($valor) >= 2 && ($abre === '"' || $abre === "'") && substr($valor, -1) === $abre) {
                $valor = substr($valor, 1, -1);
            }

            self::$variaveis[$chave] = $valor;
        }
    }

    public static function obter(string $chave, ?string $padrao = null): ?string
    {
        $doServidor = getenv($chave);

        if ($doServidor !== false) {
            return $doServidor;
        }

        return self::$variaveis[$chave] ?? $padrao;
    }

    public static function booleano(string $chave, bool $padrao = false): bool
    {
        $valor = self::obter($chave);

        if ($valor === null) {
            return $padrao;
        }

        return in_array(strtolower($valor), ['1', 'true', 'sim', 'on'], true);
    }
}
