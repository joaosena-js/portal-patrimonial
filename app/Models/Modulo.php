<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Módulos de conteúdo (texto, vídeo, documento ou link) de uma trilha. */
final class Modulo extends Model
{
    private const COLUNAS = 'id, trilha_id, titulo, descricao, tipo, conteudo_url, conteudo_texto,
                             ordem, carga_horaria_min, ativo, atualizado_em';

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm(
            'SELECT ' . self::COLUNAS . ' FROM modulos WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    /** @return list<array<string, mixed>> */
    public function listarPorTrilha(int $trilhaId, bool $apenasAtivos): array
    {
        return $this->consultar(
            'SELECT ' . self::COLUNAS . '
             FROM modulos
             WHERE trilha_id = :trilha_id' . ($apenasAtivos ? ' AND ativo = 1' : '') . '
             ORDER BY ordem, id',
            ['trilha_id' => $trilhaId]
        );
    }

    public function contarAtivos(int $trilhaId): int
    {
        $linha = $this->consultarUm(
            'SELECT COUNT(*) AS total FROM modulos WHERE trilha_id = :trilha_id AND ativo = 1',
            ['trilha_id' => $trilhaId]
        );

        return (int) ($linha['total'] ?? 0);
    }

    public function proximaOrdem(int $trilhaId): int
    {
        $linha = $this->consultarUm(
            'SELECT COALESCE(MAX(ordem), 0) + 1 AS proxima FROM modulos WHERE trilha_id = :trilha_id',
            ['trilha_id' => $trilhaId]
        );

        return (int) ($linha['proxima'] ?? 1);
    }

    /**
     * @param array{trilha_id: int, titulo: string, descricao: ?string, tipo: string, conteudo_url: ?string,
     *              conteudo_texto: ?string, ordem: int, carga_horaria_min: ?int, ativo: int} $dados
     */
    public function criar(array $dados): int
    {
        $this->executar(
            'INSERT INTO modulos
                (trilha_id, titulo, descricao, tipo, conteudo_url, conteudo_texto, ordem, carga_horaria_min, ativo)
             VALUES
                (:trilha_id, :titulo, :descricao, :tipo, :conteudo_url, :conteudo_texto, :ordem, :carga_horaria_min, :ativo)',
            $dados
        );

        return $this->ultimoId();
    }

    /**
     * @param array{titulo: string, descricao: ?string, tipo: string, conteudo_url: ?string,
     *              conteudo_texto: ?string, ordem: int, carga_horaria_min: ?int, ativo: int} $dados
     */
    public function atualizar(int $id, array $dados): void
    {
        $this->executar(
            'UPDATE modulos
             SET titulo = :titulo, descricao = :descricao, tipo = :tipo, conteudo_url = :conteudo_url,
                 conteudo_texto = :conteudo_texto, ordem = :ordem, carga_horaria_min = :carga_horaria_min, ativo = :ativo
             WHERE id = :id',
            $dados + ['id' => $id]
        );
    }
}
