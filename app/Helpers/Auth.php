<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Usuario;

/**
 * Usuário autenticado da requisição.
 * A sessão guarda apenas o id. O restante é lido do banco, assim um usuário
 * desativado perde o acesso imediatamente.
 */
final class Auth
{
    private const CHAVE = 'usuario_id';

    private static ?array $cache = null;
    private static bool $carregado = false;

    /** @return array<string, mixed>|null */
    public static function usuario(): ?array
    {
        if (self::$carregado) {
            return self::$cache;
        }

        self::$carregado = true;
        $id = Session::obter(self::CHAVE);

        if (!is_int($id)) {
            return self::$cache = null;
        }

        $usuario = (new Usuario())->buscarPorId($id);

        if ($usuario === null || $usuario['situacao'] !== 'ativo') {
            Session::remover(self::CHAVE);

            return self::$cache = null;
        }

        return self::$cache = $usuario;
    }

    public static function entrar(int $usuarioId): void
    {
        Session::regenerar();
        Csrf::renovar();
        Session::definir(self::CHAVE, $usuarioId);

        self::$cache = null;
        self::$carregado = false;
    }

    public static function sair(): void
    {
        Session::destruir();

        self::$cache = null;
        self::$carregado = false;
    }
}
