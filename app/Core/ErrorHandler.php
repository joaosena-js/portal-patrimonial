<?php

declare(strict_types=1);

namespace App\Core;

use ErrorException;
use Throwable;

/**
 * Tratamento centralizado de erros: registra em log e mostra uma página amigável,
 * sem expor detalhes internos (exceto com APP_DEBUG=true).
 */
final class ErrorHandler
{
    private const PAGINAS = [403, 404, 413, 419, 500];

    public static function registrar(): void
    {
        set_error_handler([self::class, 'tratarErro']);
        set_exception_handler([self::class, 'tratarExcecao']);
        register_shutdown_function([self::class, 'tratarEncerramento']);
    }

    public static function tratarErro(int $nivel, string $mensagem, string $arquivo, int $linha): bool
    {
        if (($nivel & (E_DEPRECATED | E_USER_DEPRECATED)) !== 0) {
            Logger::warning('Recurso obsoleto: ' . $mensagem, ['arquivo' => $arquivo, 'linha' => $linha]);

            return true;
        }

        if ((error_reporting() & $nivel) === 0) {
            return false;
        }

        throw new ErrorException($mensagem, 0, $nivel, $arquivo, $linha);
    }

    public static function tratarExcecao(Throwable $e): void
    {
        if ($e instanceof HttpException) {
            if ($e->codigoHttp() === 403) {
                Logger::warning('Acesso negado', ['url' => $_SERVER['REQUEST_URI'] ?? '', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            }

            self::exibir($e->codigoHttp());

            return;
        }

        Logger::error(get_class($e) . ': ' . $e->getMessage(), [
            'arquivo' => $e->getFile(),
            'linha' => $e->getLine(),
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'metodo' => $_SERVER['REQUEST_METHOD'] ?? '',
        ]);

        self::exibir(500, Config::obter('debug') ? get_class($e) . ': ' . $e->getMessage() : null);
    }

    public static function tratarEncerramento(): void
    {
        $erro = error_get_last();

        if ($erro !== null && in_array($erro['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            Logger::error('Erro fatal: ' . $erro['message'], ['arquivo' => $erro['file'], 'linha' => $erro['line']]);
        }
    }

    private static function exibir(int $codigo, ?string $detalhe = null): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code(in_array($codigo, self::PAGINAS, true) ? $codigo : 500);
        }

        $view = in_array($codigo, self::PAGINAS, true) ? 'erros/' . $codigo : 'erros/500';

        echo View::renderizar($view, ['titulo' => 'Erro', 'detalhe' => $detalhe], 'auth');
    }
}
