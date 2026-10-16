<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Avisos e comunicados da CPAT para os gestores. */
final class Aviso extends Model
{
    /**
     * Avisos ativos e dentro da validade.
     *
     * @return list<array<string, mixed>>
     */
    public function listarVisiveis(int $limite): array
    {
        return $this->consultar(
            'SELECT a.id, a.titulo, a.mensagem, a.urgente, a.publicado_em, a.expira_em, u.nome AS autor
             FROM avisos a
             LEFT JOIN usuarios u ON u.id = a.publicado_por
             WHERE a.ativo = 1 AND (a.expira_em IS NULL OR a.expira_em >= CURDATE())
             ORDER BY a.urgente DESC, a.publicado_em DESC
             LIMIT :limite',
            ['limite' => $limite]
        );
    }

    /**
     * Todos os avisos, inclusive os desativados e vencidos (área administrativa).
     *
     * @return list<array<string, mixed>>
     */
    public function listarTodos(int $limite): array
    {
        return $this->consultar(
            'SELECT a.id, a.titulo, a.mensagem, a.urgente, a.publicado_em, a.expira_em, a.ativo, u.nome AS autor,
                    (a.ativo = 1 AND (a.expira_em IS NULL OR a.expira_em >= CURDATE())) AS visivel
             FROM avisos a
             LEFT JOIN usuarios u ON u.id = a.publicado_por
             ORDER BY a.publicado_em DESC
             LIMIT :limite',
            ['limite' => $limite]
        );
    }

    /** @param array{titulo: string, mensagem: string, urgente: int, expira_em: ?string, publicado_por: int} $dados */
    public function criar(array $dados): int
    {
        $this->executar(
            'INSERT INTO avisos (titulo, mensagem, urgente, expira_em, publicado_por)
             VALUES (:titulo, :mensagem, :urgente, :expira_em, :publicado_por)',
            $dados
        );

        return $this->ultimoId();
    }

    public function contarVisiveis(): int
    {
        $linha = $this->consultarUm(
            'SELECT COUNT(*) AS total FROM avisos WHERE ativo = 1 AND (expira_em IS NULL OR expira_em >= CURDATE())'
        );

        return (int) ($linha['total'] ?? 0);
    }

    /** @return list<array<string, mixed>> */
    public function buscarTexto(string $termo, int $limite): array
    {
        $like = '%' . addcslashes($termo, '%_\\') . '%';

        return $this->consultar(
            'SELECT id, titulo, mensagem, urgente, publicado_em
             FROM avisos
             WHERE ativo = 1 AND (expira_em IS NULL OR expira_em >= CURDATE())
               AND (titulo LIKE :termo_titulo OR mensagem LIKE :termo_mensagem)
             ORDER BY publicado_em DESC
             LIMIT :limite',
            ['termo_titulo' => $like, 'termo_mensagem' => $like, 'limite' => $limite]
        );
    }

    public function desativar(int $id): bool
    {
        return $this->executar('UPDATE avisos SET ativo = 0 WHERE id = :id AND ativo = 1', ['id' => $id]) > 0;
    }
}
