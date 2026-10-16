<?php

declare(strict_types=1);

namespace App\Core;

/** Acesso somente leitura às configurações de config/config.php. */
final class Config
{
    /** @var array<string, mixed> */
    private static array $dados = [];

    /** @param array<string, mixed> $dados */
    public static function carregar(array $dados): void
    {
        self::$dados = $dados;
    }

    public static function obter(string $chave, mixed $padrao = null): mixed
    {
        return self::$dados[$chave] ?? $padrao;
    }
}
