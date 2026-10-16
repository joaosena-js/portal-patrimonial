<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Notificações de novidades. Há uma linha por novidade publicada; a leitura é registrada por usuário
 * em notificacoes_lidas. Cada usuário só vê novidades criadas depois do seu cadastro.
 */
final class Notificacao extends Model
{
    /** Ignora novidades cujo conteúdo foi ocultado depois da publicação. */
    private const CONTEUDO_VISIVEL = "(CASE n.tipo
            WHEN 'video' THEN EXISTS (SELECT 1 FROM videos x WHERE x.id = n.entidade_id AND x.ativo = 1)
            WHEN 'material' THEN EXISTS (SELECT 1 FROM materiais x WHERE x.id = n.entidade_id AND x.ativo = 1)
            WHEN 'legislacao' THEN EXISTS (SELECT 1 FROM legislacoes x WHERE x.id = n.entidade_id AND x.ativo = 1)
            WHEN 'faq' THEN EXISTS (SELECT 1 FROM faqs x WHERE x.id = n.entidade_id AND x.ativo = 1)
            WHEN 'aviso' THEN EXISTS (SELECT 1 FROM avisos x WHERE x.id = n.entidade_id AND x.ativo = 1)
            ELSE 1
        END)";

    public function criar(string $tipo, string $titulo, ?int $entidadeId, bool $urgente = false): int
    {
        $this->executar(
            'INSERT INTO notificacoes (tipo, titulo, entidade_id, urgente) VALUES (:tipo, :titulo, :entidade_id, :urgente)',
            [
                'tipo' => $tipo,
                'titulo' => mb_substr($titulo, 0, 190),
                'entidade_id' => $entidadeId,
                'urgente' => $urgente ? 1 : 0,
            ]
        );

        return $this->ultimoId();
    }

    /**
     * Novidades dos últimos 30 dias que o usuário ainda não visualizou. Urgentes primeiro.
     *
     * @return list<array<string, mixed>>
     */
    public function naoLidas(int $usuarioId, int $limite): array
    {
        return $this->consultar(
            'SELECT n.id, n.tipo, n.titulo, n.entidade_id, n.urgente, n.criado_em
             FROM notificacoes n
             JOIN usuarios u ON u.id = :usuario_id
             WHERE n.criado_em > u.criado_em
               AND n.criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               AND NOT EXISTS (
                   SELECT 1 FROM notificacoes_lidas l WHERE l.notificacao_id = n.id AND l.usuario_id = :usuario_lido
               )
               AND ' . self::CONTEUDO_VISIVEL . '
             ORDER BY n.urgente DESC, n.criado_em DESC
             LIMIT :limite',
            ['usuario_id' => $usuarioId, 'usuario_lido' => $usuarioId, 'limite' => $limite]
        );
    }

    /**
     * Histórico dos últimos 60 dias, com o indicador "nova" (ainda não visualizada).
     *
     * @return list<array<string, mixed>>
     */
    public function recentes(int $usuarioId, int $limite): array
    {
        return $this->consultar(
            'SELECT n.id, n.tipo, n.titulo, n.entidade_id, n.urgente, n.criado_em,
                    (l.notificacao_id IS NULL) AS nova
             FROM notificacoes n
             JOIN usuarios u ON u.id = :usuario_id
             LEFT JOIN notificacoes_lidas l ON l.notificacao_id = n.id AND l.usuario_id = :usuario_lido
             WHERE n.criado_em > u.criado_em
               AND n.criado_em >= DATE_SUB(NOW(), INTERVAL 60 DAY)
               AND ' . self::CONTEUDO_VISIVEL . '
             ORDER BY n.criado_em DESC
             LIMIT :limite',
            ['usuario_id' => $usuarioId, 'usuario_lido' => $usuarioId, 'limite' => $limite]
        );
    }

    /** @param list<int> $notificacaoIds */
    public function marcarLidas(int $usuarioId, array $notificacaoIds): void
    {
        foreach ($notificacaoIds as $id) {
            $this->executar(
                'INSERT IGNORE INTO notificacoes_lidas (usuario_id, notificacao_id) VALUES (:usuario_id, :notificacao_id)',
                ['usuario_id' => $usuarioId, 'notificacao_id' => (int) $id]
            );
        }
    }
}
