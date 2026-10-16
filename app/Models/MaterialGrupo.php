<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Grupos (assuntos) que organizam os materiais em accordion, ex.: "Transferência entre Órgãos". */
final class MaterialGrupo extends Model
{
    /** @return list<array<string, mixed>> */
    public function listarAtivos(): array
    {
        return $this->consultar('SELECT id, titulo, descricao, ordem FROM material_grupos WHERE ativo = 1 ORDER BY ordem, titulo');
    }

    /** @return list<array<string, mixed>> */
    public function listarTodos(): array
    {
        return $this->consultar(
            'SELECT g.id, g.titulo, g.descricao, g.ordem, g.ativo, COUNT(m.id) AS total_materiais
             FROM material_grupos g
             LEFT JOIN materiais m ON m.grupo_id = g.id
             GROUP BY g.id, g.titulo, g.descricao, g.ordem, g.ativo
             ORDER BY g.ordem, g.titulo'
        );
    }

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm('SELECT id, titulo, descricao, ordem, ativo FROM material_grupos WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function proximaOrdem(): int
    {
        return (int) ($this->consultarUm('SELECT COALESCE(MAX(ordem), 0) + 1 AS proxima FROM material_grupos')['proxima'] ?? 1);
    }

    public function criar(string $titulo, ?string $descricao, int $ordem): int
    {
        $this->executar(
            'INSERT INTO material_grupos (titulo, descricao, ordem) VALUES (:titulo, :descricao, :ordem)',
            ['titulo' => $titulo, 'descricao' => $descricao, 'ordem' => $ordem]
        );

        return $this->ultimoId();
    }

    public function atualizar(int $id, string $titulo, ?string $descricao, int $ordem, int $ativo): void
    {
        $this->executar(
            'UPDATE material_grupos SET titulo = :titulo, descricao = :descricao, ordem = :ordem, ativo = :ativo WHERE id = :id',
            ['titulo' => $titulo, 'descricao' => $descricao, 'ordem' => $ordem, 'ativo' => $ativo, 'id' => $id]
        );
    }
}
