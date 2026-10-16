<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;

/**
 * Entrega um arquivo guardado em storage/uploads ao navegador.
 * O tipo de conteúdo vem de uma lista fixa (nunca do arquivo), e o vídeo aceita
 * pedidos parciais (Range), necessários para avançar e retroceder no player.
 */
final class EnvioArquivo
{
    private const TIPOS = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
    ];

    /** @param array<string, mixed> $arquivo Linha da tabela "arquivos". Encerra a requisição ao final. */
    public static function enviar(array $arquivo, bool $exibirNoNavegador): void
    {
        $caminho = BASE_PATH . '/storage/uploads/' . $arquivo['caminho'];

        if (!is_file($caminho)) {
            throw new HttpException(404, 'Arquivo ausente no servidor.');
        }

        $tamanho = (int) filesize($caminho);
        $inicio = 0;
        $fim = $tamanho - 1;
        $status = 200;

        // Libera a sessão: sem isso, um vídeo em reprodução bloquearia as demais páginas do usuário.
        session_write_close();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if ($exibirNoNavegador && preg_match('/^bytes=(\d*)-(\d*)$/', (string) ($_SERVER['HTTP_RANGE'] ?? ''), $m) === 1) {
            if ($m[1] === '' && $m[2] !== '') {
                $inicio = max(0, $tamanho - (int) $m[2]);
            } else {
                $inicio = (int) $m[1];
                $fim = $m[2] !== '' ? min((int) $m[2], $tamanho - 1) : $fim;
            }

            if ($inicio > $fim || $inicio >= $tamanho) {
                http_response_code(416);
                header('Content-Range: bytes */' . $tamanho);
                exit;
            }

            $status = 206;
        }

        $nomeSeguro = preg_replace('/[^\w.\- ]/u', '_', (string) $arquivo['nome_original']);

        http_response_code($status);
        header('Content-Type: ' . (self::TIPOS[$arquivo['extensao']] ?? 'application/octet-stream'));
        header('X-Content-Type-Options: nosniff');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . ($fim - $inicio + 1));
        header(
            'Content-Disposition: ' . ($exibirNoNavegador ? 'inline' : 'attachment')
            . '; filename="' . $nomeSeguro . '"; filename*=UTF-8\'\'' . rawurlencode((string) $arquivo['nome_original'])
        );

        if ($status === 206) {
            header('Content-Range: bytes ' . $inicio . '-' . $fim . '/' . $tamanho);
        }

        $manipulador = fopen($caminho, 'rb');
        fseek($manipulador, $inicio);
        $restante = $fim - $inicio + 1;

        while ($restante > 0 && !feof($manipulador)) {
            $bloco = fread($manipulador, min(8192, $restante));

            if ($bloco === false) {
                break;
            }

            echo $bloco;
            $restante -= strlen($bloco);
            flush();
        }

        fclose($manipulador);
        exit;
    }
}
