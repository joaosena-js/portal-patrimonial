<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Logger;
use App\Models\Arquivo;
use DomainException;

/**
 * Recebe e guarda arquivos enviados pelos administradores.
 *
 * Proteções: lista fixa de extensões, conferência da assinatura (bytes iniciais) do arquivo,
 * limite de tamanho, nome de gravação aleatório e pasta fora do diretório público.
 * O nome original só é usado para exibição e para o download.
 */
final class ArmazenamentoArquivos
{
    private const CONTEXTOS = [
        'documento' => ['extensoes' => ['pdf', 'docx', 'xlsx'], 'limite' => 'upload_documento_mb', 'rotulo' => 'PDF, DOCX ou XLSX'],
        'video' => ['extensoes' => ['mp4', 'webm'], 'limite' => 'upload_video_mb', 'rotulo' => 'MP4 ou WebM'],
    ];

    /** Indica se o formulário trouxe um arquivo (campo preenchido). */
    public static function temEnvio(?array $envio): bool
    {
        return $envio !== null && ($envio['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    public static function limiteMb(string $contexto): int
    {
        return max(1, (int) Config::obter(self::CONTEXTOS[$contexto]['limite'], 20));
    }

    /**
     * Valida e grava o arquivo. Devolve o id do registro em "arquivos".
     * As DomainException trazem mensagens próprias para exibir ao usuário.
     */
    public function salvar(?array $envio, string $contexto, int $usuarioId): int
    {
        if (!self::temEnvio($envio)) {
            throw new DomainException('Selecione um arquivo para enviar.');
        }

        if ($envio['error'] !== UPLOAD_ERR_OK) {
            throw new DomainException($this->mensagemDeErro((int) $envio['error']));
        }

        if (!is_uploaded_file((string) $envio['tmp_name'])) {
            throw new DomainException('Envio de arquivo inválido.');
        }

        $regra = self::CONTEXTOS[$contexto];
        $limiteMb = self::limiteMb($contexto);

        if ((int) $envio['size'] > $limiteMb * 1048576) {
            throw new DomainException('O arquivo excede o limite de ' . $limiteMb . ' MB.');
        }

        $nomeOriginal = $this->limparNome((string) $envio['name']);
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (!in_array($extensao, $regra['extensoes'], true)) {
            throw new DomainException('Formato não permitido. Envie ' . $regra['rotulo'] . '.');
        }

        if (!$this->assinaturaValida((string) $envio['tmp_name'], $extensao)) {
            throw new DomainException('O conteúdo do arquivo não corresponde ao formato ' . strtoupper($extensao) . '.');
        }

        $subpasta = date('Y') . '/' . date('m');
        $pasta = BASE_PATH . '/storage/uploads/' . $subpasta;

        if (!is_dir($pasta) && !@mkdir($pasta, 0750, true) && !is_dir($pasta)) {
            Logger::error('Não foi possível criar a pasta de uploads', ['pasta' => $pasta]);

            throw new DomainException('Não foi possível gravar o arquivo no servidor.');
        }

        $nomeGravado = bin2hex(random_bytes(16)) . '.' . $extensao;

        if (!move_uploaded_file((string) $envio['tmp_name'], $pasta . '/' . $nomeGravado)) {
            Logger::error('Falha ao mover arquivo enviado', ['destino' => $subpasta . '/' . $nomeGravado]);

            throw new DomainException('Não foi possível gravar o arquivo no servidor.');
        }

        return (new Arquivo())->criar([
            'nome_original' => $nomeOriginal,
            'caminho' => $subpasta . '/' . $nomeGravado,
            'extensao' => $extensao,
            'tamanho' => (int) $envio['size'],
            'enviado_por' => $usuarioId,
        ]);
    }

    /** Remove o registro e o arquivo físico (usado ao substituir um arquivo). */
    public function excluir(int $arquivoId): void
    {
        $modelo = new Arquivo();
        $arquivo = $modelo->buscarPorId($arquivoId);

        if ($arquivo === null) {
            return;
        }

        $modelo->excluir($arquivoId);
        @unlink(BASE_PATH . '/storage/uploads/' . $arquivo['caminho']);
    }

    private function limparNome(string $nome): string
    {
        $nome = basename(str_replace('\\', '/', $nome));
        $nome = preg_replace('/[\x00-\x1F\x7F]/u', '', $nome) ?? '';
        $nome = mb_substr(trim($nome), 0, 190);

        return $nome === '' ? 'arquivo' : $nome;
    }

    /** Confere os primeiros bytes do arquivo com o formato declarado pela extensão. */
    private function assinaturaValida(string $caminho, string $extensao): bool
    {
        $manipulador = @fopen($caminho, 'rb');

        if ($manipulador === false) {
            return false;
        }

        $inicio = (string) fread($manipulador, 12);
        fclose($manipulador);

        return match ($extensao) {
            'pdf' => strncmp($inicio, '%PDF-', 5) === 0,
            'docx', 'xlsx' => strncmp($inicio, "PK\x03\x04", 4) === 0,
            'mp4' => substr($inicio, 4, 4) === 'ftyp',
            'webm' => strncmp($inicio, "\x1A\x45\xDF\xA3", 4) === 0,
            default => false,
        };
    }

    private function mensagemDeErro(int $codigo): string
    {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o limite do servidor (upload_max_filesize e post_max_size no php.ini).',
            UPLOAD_ERR_PARTIAL => 'O envio foi interrompido. Tente novamente.',
            default => 'Não foi possível receber o arquivo. Tente novamente.',
        };
    }
}
