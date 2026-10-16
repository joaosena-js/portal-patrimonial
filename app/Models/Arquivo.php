<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Registro dos arquivos enviados (o conteúdo fica em storage/uploads). */
final class Arquivo extends Model
{
    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm(
            'SELECT id, nome_original, caminho, extensao, tamanho FROM arquivos WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    /** @param array{nome_original: string, caminho: string, extensao: string, tamanho: int, enviado_por: int} $dados */
    public function criar(array $dados): int
    {
        $this->executar(
            'INSERT INTO arquivos (nome_original, caminho, extensao, tamanho, enviado_por)
             VALUES (:nome_original, :caminho, :extensao, :tamanho, :enviado_por)',
            $dados
        );

        return $this->ultimoId();
    }

    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM arquivos WHERE id = :id', ['id' => $id]);
    }
}
