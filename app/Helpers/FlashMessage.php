<?php

declare(strict_types=1);

namespace App\Helpers;

/** Mensagens exibidas uma única vez na próxima página. */
final class FlashMessage
{
    private const CHAVE = '_flash';

    public static function adicionar(string $tipo, string $mensagem): void
    {
        $_SESSION[self::CHAVE][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    public static function sucesso(string $mensagem): void
    {
        self::adicionar('sucesso', $mensagem);
    }

    public static function erro(string $mensagem): void
    {
        self::adicionar('erro', $mensagem);
    }

    public static function aviso(string $mensagem): void
    {
        self::adicionar('aviso', $mensagem);
    }

    /** @return list<array{tipo: string, mensagem: string}> */
    public static function obterTodas(): array
    {
        $mensagens = $_SESSION[self::CHAVE] ?? [];
        unset($_SESSION[self::CHAVE]);

        return $mensagens;
    }
}
