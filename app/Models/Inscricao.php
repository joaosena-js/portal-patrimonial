<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Participação de um gestor em uma trilha. */
final class Inscricao extends Model
{
    /** @return array<string, mixed>|null */
    public function buscar(int $usuarioId, int $trilhaId): ?array
    {
        return $this->consultarUm(
            'SELECT id, usuario_id, trilha_id, situacao, iniciada_em, concluida_em
             FROM inscricoes
             WHERE usuario_id = :usuario_id AND trilha_id = :trilha_id
             LIMIT 1',
            ['usuario_id' => $usuarioId, 'trilha_id' => $trilhaId]
        );
    }

    /** Cria a inscrição se ainda não existir. Devolve true quando foi criada agora. */
    public function inscrever(int $usuarioId, int $trilhaId): bool
    {
        return $this->executar(
            'INSERT IGNORE INTO inscricoes (usuario_id, trilha_id) VALUES (:usuario_id, :trilha_id)',
            ['usuario_id' => $usuarioId, 'trilha_id' => $trilhaId]
        ) > 0;
    }

    public function marcarConcluida(int $usuarioId, int $trilhaId): void
    {
        $this->executar(
            "UPDATE inscricoes
             SET situacao = 'concluida', concluida_em = NOW()
             WHERE usuario_id = :usuario_id AND trilha_id = :trilha_id AND situacao <> 'concluida'",
            ['usuario_id' => $usuarioId, 'trilha_id' => $trilhaId]
        );
    }

    /**
     * Inscritos e concluintes por trilha publicada.
     *
     * @return list<array<string, mixed>>
     */
    public function resumoPorTrilha(): array
    {
        return $this->consultar(
            "SELECT t.id, t.titulo, COUNT(i.id) AS inscritos,
                    COALESCE(SUM(i.situacao = 'concluida'), 0) AS concluidos
             FROM trilhas t
             LEFT JOIN inscricoes i ON i.trilha_id = t.id
             WHERE t.situacao = 'publicada'
             GROUP BY t.id, t.titulo
             ORDER BY t.titulo"
        );
    }

    /** @return array{inscricoes: int, concluidas: int} */
    public function totais(): array
    {
        $linha = $this->consultarUm(
            "SELECT COUNT(*) AS inscricoes, COALESCE(SUM(situacao = 'concluida'), 0) AS concluidas FROM inscricoes"
        );

        return [
            'inscricoes' => (int) ($linha['inscricoes'] ?? 0),
            'concluidas' => (int) ($linha['concluidas'] ?? 0),
        ];
    }
}
