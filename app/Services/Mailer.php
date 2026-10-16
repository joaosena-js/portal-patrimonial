<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Logger;

/**
 * Envio de e-mails de texto simples.
 * Driver "log": grava em storage/logs/emails.log (padrão, serve para testes e para o XAMPP).
 * Driver "mail": usa mail() do PHP, que exige servidor de e-mail configurado.
 * Para produção, o Departamento de Tecnologia deve informar o SMTP institucional.
 */
final class Mailer
{
    public function enviar(string $para, string $assunto, string $mensagem): bool
    {
        if (filter_var($para, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        // Remove quebras de linha para impedir injeção de cabeçalhos.
        $assunto = trim(str_replace(["\r", "\n"], ' ', $assunto));
        $de = trim(str_replace(["\r", "\n"], '', (string) Config::obter('mail_from', 'nao-responda@localhost')));

        if (Config::obter('mail_driver') === 'mail') {
            $cabecalhos = "From: {$de}\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8";
            $enviado = @mail($para, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $mensagem, $cabecalhos);

            if (!$enviado) {
                Logger::error('Falha ao enviar e-mail', ['assunto' => $assunto]);
            }

            return $enviado;
        }

        $registro = sprintf("[%s] Para: %s\nAssunto: %s\n\n%s\n%s\n", date('Y-m-d H:i:s'), $para, $assunto, $mensagem, str_repeat('-', 60));

        return @file_put_contents(BASE_PATH . '/storage/logs/emails.log', $registro, FILE_APPEND | LOCK_EX) !== false;
    }
}
