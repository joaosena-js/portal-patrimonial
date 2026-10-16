<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Trilhas de capacitação. Cada trilha reúne módulos de conteúdo. */
final class Trilha extends Model
{
    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm(
            'SELECT id, titulo, descricao, area, situacao, criado_em, atualizado_em
             FROM trilhas WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    /**
     * Todas as trilhas para a área administrativa, com a quantidade de módulos ativos.
     *
     * @return list<array<string, mixed>>
     */
    public function listarParaAdmin(): array
    {
        return $this->consultar(
            'SELECT t.id, t.titulo, t.area, t.situacao, t.atualizado_em,
                    COUNT(m.id) AS total_modulos
             FROM trilhas t
             LEFT JOIN modulos m ON m.trilha_id = t.id AND m.ativo = 1
             GROUP BY t.id, t.titulo, t.area, t.situacao, t.atualizado_em
             ORDER BY t.situacao = \'publicada\' DESC, t.titulo'
        );
    }

    /**
     * Trilhas publicadas com o andamento do gestor, em uma única consulta.
     *
     * @return list<array<string, mixed>>
     */
    public function listarPublicadasParaGestor(int $usuarioId): array
    {
        return $this->consultar(
            "SELECT t.id, t.titulo, t.descricao, t.area,
                    i.situacao AS inscricao_situacao,
                    (SELECT COUNT(*) FROM modulos m
                      WHERE m.trilha_id = t.id AND m.ativo = 1) AS total_modulos,
                    (SELECT COUNT(*) FROM progresso p
                       JOIN modulos m2 ON m2.id = p.modulo_id
                      WHERE m2.trilha_id = t.id AND m2.ativo = 1 AND p.usuario_id = :usuario_progresso) AS modulos_concluidos
             FROM trilhas t
             LEFT JOIN inscricoes i ON i.trilha_id = t.id AND i.usuario_id = :usuario_inscricao
             WHERE t.situacao = 'publicada'
             ORDER BY t.titulo",
            ['usuario_progresso' => $usuarioId, 'usuario_inscricao' => $usuarioId]
        );
    }

    /** @param array{titulo: string, descricao: ?string, area: string, criado_por: int} $dados */
    public function criar(array $dados): int
    {
        $this->executar(
            "INSERT INTO trilhas (titulo, descricao, area, situacao, criado_por)
             VALUES (:titulo, :descricao, :area, 'rascunho', :criado_por)",
            $dados
        );

        return $this->ultimoId();
    }

    /** @param array{titulo: string, descricao: ?string, area: string, situacao: string} $dados */
    public function atualizar(int $id, array $dados): void
    {
        $this->executar(
            'UPDATE trilhas
             SET titulo = :titulo, descricao = :descricao, area = :area, situacao = :situacao
             WHERE id = :id',
            $dados + ['id' => $id]
        );
    }

    /**
     * Busca por texto nas trilhas publicadas (pesquisa global).
     *
     * @return list<array<string, mixed>>
     */
    public function buscarPublicadas(string $termo, int $limite): array
    {
        $like = '%' . addcslashes($termo, '%_\\') . '%';

        return $this->consultar(
            "SELECT id, titulo, descricao, area
             FROM trilhas
             WHERE situacao = 'publicada' AND (titulo LIKE :termo_titulo OR descricao LIKE :termo_descricao)
             ORDER BY titulo
             LIMIT :limite",
            ['termo_titulo' => $like, 'termo_descricao' => $like, 'limite' => $limite]
        );
    }

    public function contarPorSituacao(string $situacao): int
    {
        $linha = $this->consultarUm(
            'SELECT COUNT(*) AS total FROM trilhas WHERE situacao = :situacao',
            ['situacao' => $situacao]
        );

        return (int) ($linha['total'] ?? 0);
    }
}
