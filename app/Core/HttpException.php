<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Exceção para respostas HTTP esperadas (403, 404, 419...).
 * Tratada pelo ErrorHandler, que exibe a página de erro correspondente.
 */
final class HttpException extends RuntimeException
{
    public function __construct(private int $codigoHttp, string $mensagem = '')
    {
        parent::__construct($mensagem, $codigoHttp);
    }

    public function codigoHttp(): int
    {
        return $this->codigoHttp;
    }
}
