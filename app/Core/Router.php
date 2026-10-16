<?php

declare(strict_types=1);

namespace App\Core;

use App\Helpers\Csrf;

/**
 * Roteador por correspondência exata de caminho e método HTTP.
 * Identificadores trafegam em query string (GET) ou campos do formulário (POST).
 */
final class Router
{
    /** @param array<string, array<string, array{0: string, 1: string}>> $rotas */
    public function __construct(private array $rotas)
    {
    }

    public function despachar(string $metodo, string $caminho): void
    {
        $metodo = strtoupper($metodo);

        if (!isset($this->rotas[$metodo][$caminho])) {
            throw new HttpException(404, 'Rota não encontrada: ' . $metodo . ' ' . $caminho);
        }

        // Envio maior que post_max_size chega sem nenhum campo: informa o limite em vez de falhar no CSRF.
        if ($metodo === 'POST' && $_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            throw new HttpException(413, 'Corpo da requisição acima de post_max_size.');
        }

        // Toda requisição que altera dados exige o token CSRF.
        if ($metodo === 'POST') {
            Csrf::validarRequisicao();
        }

        [$controller, $acao] = $this->rotas[$metodo][$caminho];

        $classe = 'App\\Controllers\\' . $controller;

        (new $classe())->$acao();
    }
}
