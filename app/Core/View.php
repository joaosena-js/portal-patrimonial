<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use Throwable;

/**
 * Renderização de views com layout.
 * A view é gerada primeiro e entregue ao layout na variável $conteudo.
 */
final class View
{
    public static function renderizar(string $view, array $dados = [], string $layout = 'app'): string
    {
        $conteudo = self::capturar(self::arquivo($view), $dados);

        if ($layout === '') {
            return $conteudo;
        }

        return self::capturar(self::arquivo('layouts/' . $layout), $dados + ['conteudo' => $conteudo]);
    }

    private static function arquivo(string $view): string
    {
        // Nomes de view vêm sempre do código, mas a validação evita path traversal por engano.
        if (!preg_match('#^[A-Za-z0-9_/]+$#', $view)) {
            throw new InvalidArgumentException('Nome de view inválido.');
        }

        $arquivo = BASE_PATH . '/app/Views/' . $view . '.php';

        if (!is_file($arquivo)) {
            throw new InvalidArgumentException('View não encontrada: ' . $view);
        }

        return $arquivo;
    }

    private static function capturar(string $arquivo, array $dados): string
    {
        $executar = static function (string $__arquivo, array $__dados): void {
            extract($__dados, EXTR_SKIP);
            include $__arquivo;
        };

        ob_start();

        try {
            $executar($arquivo, $dados);
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return (string) ob_get_clean();
    }
}
