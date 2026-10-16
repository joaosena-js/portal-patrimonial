<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Órgãos e unidades da Administração Estadual aos quais os gestores são vinculados. */
final class OrgaoUnidade extends Model
{
    /** @return list<array<string, mixed>> */
    public function listarAtivos(): array
    {
        return $this->consultar("SELECT id, sigla, nome FROM orgaos_unidades WHERE situacao = 'ativo' ORDER BY sigla");
    }

    /**
     * Todos os órgãos, com a quantidade de usuários vinculados.
     *
     * @return list<array<string, mixed>>
     */
    public function listarTodos(): array
    {
        return $this->consultar(
            'SELECT o.id, o.sigla, o.nome, o.situacao, COUNT(u.id) AS total_usuarios
             FROM orgaos_unidades o
             LEFT JOIN usuarios u ON u.orgao_id = o.id
             GROUP BY o.id, o.sigla, o.nome, o.situacao
             ORDER BY o.sigla'
        );
    }

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm('SELECT id, sigla, nome, situacao FROM orgaos_unidades WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    /** @return array<string, mixed>|null */
    public function buscarAtivoPorId(int $id): ?array
    {
        return $this->consultarUm(
            "SELECT id, sigla, nome FROM orgaos_unidades WHERE id = :id AND situacao = 'ativo' LIMIT 1",
            ['id' => $id]
        );
    }

    public function siglaExiste(string $sigla, ?int $ignorarId = null): bool
    {
        return $this->consultarUm(
            'SELECT id FROM orgaos_unidades WHERE sigla = :sigla AND id <> :ignorar LIMIT 1',
            ['sigla' => $sigla, 'ignorar' => $ignorarId ?? 0]
        ) !== null;
    }

    public function criar(string $sigla, string $nome): int
    {
        $this->executar('INSERT INTO orgaos_unidades (sigla, nome) VALUES (:sigla, :nome)', ['sigla' => $sigla, 'nome' => $nome]);

        return $this->ultimoId();
    }

    public function atualizar(int $id, string $sigla, string $nome, string $situacao): void
    {
        $this->executar(
            'UPDATE orgaos_unidades SET sigla = :sigla, nome = :nome, situacao = :situacao WHERE id = :id',
            ['sigla' => $sigla, 'nome' => $nome, 'situacao' => $situacao, 'id' => $id]
        );
    }

    public function contarAtivos(): int
    {
        return (int) ($this->consultarUm("SELECT COUNT(*) AS total FROM orgaos_unidades WHERE situacao = 'ativo'")['total'] ?? 0);
    }
}
