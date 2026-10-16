<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Módulos concluídos por cada gestor. */
final class Progresso extends Model
{
    /**
     * Ids dos módulos ativos da trilha que o gestor já concluiu.
     *
     * @return list<int>
     */
    public function idsConcluidos(int $usuarioId, int $trilhaId): array
    {
        $linhas = $this->consultar(
            'SELECT p.modulo_id
             FROM progresso p
             JOIN modulos m ON m.id = p.modulo_id
             WHERE p.usuario_id = :usuario_id AND m.trilha_id = :trilha_id AND m.ativo = 1',
            ['usuario_id' => $usuarioId, 'trilha_id' => $trilhaId]
        );

        return array_map(static fn (array $linha): int => (int) $linha['modulo_id'], $linhas);
    }

    public function registrar(int $usuarioId, int $moduloId): bool
    {
        return $this->executar(
            'INSERT IGNORE INTO progresso (usuario_id, modulo_id) VALUES (:usuario_id, :modulo_id)',
            ['usuario_id' => $usuarioId, 'modulo_id' => $moduloId]
        ) > 0;
    }
}
