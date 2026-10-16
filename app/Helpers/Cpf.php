<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Config;
use RuntimeException;

/**
 * CPF: validação e hash para identificação única.
 * O CPF nunca é gravado. Guarda-se apenas o HMAC, que impede cadastros duplicados
 * sem permitir recuperar o número a partir do banco de dados.
 */
final class Cpf
{
    public static function digitos(string $cpf): string
    {
        return preg_replace('/\D/', '', $cpf) ?? '';
    }

    /** Valida tamanho, sequências repetidas e os dois dígitos verificadores. */
    public static function valido(string $cpf): bool
    {
        $c = self::digitos($cpf);

        if (strlen($c) !== 11 || preg_match('/^(\d)\1{10}$/', $c) === 1) {
            return false;
        }

        for ($posicao = 9; $posicao < 11; $posicao++) {
            $soma = 0;

            for ($i = 0; $i < $posicao; $i++) {
                $soma += (int) $c[$i] * (($posicao + 1) - $i);
            }

            if ((int) $c[$posicao] !== ((10 * $soma) % 11) % 10) {
                return false;
            }
        }

        return true;
    }

    public static function hash(string $cpf): string
    {
        $chave = (string) Config::obter('app_key', '');

        if (strlen($chave) < 32) {
            throw new RuntimeException('APP_KEY não configurada no arquivo .env (mínimo de 32 caracteres).');
        }

        return hash_hmac('sha256', self::digitos($cpf), $chave);
    }
}
