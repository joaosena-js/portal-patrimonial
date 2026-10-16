<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Contatos institucionais da CPAT, do DBM e do DBI (uma linha por área). */
final class Contato extends Model
{
    /** @return list<array<string, mixed>> */
    public function listarTodos(): array
    {
        return $this->consultar(
            "SELECT id, area, nome, email, telefone, endereco, horario, competencias, atualizado_em
             FROM contatos
             ORDER BY FIELD(area, 'cpat', 'dbm', 'dbi'), nome"
        );
    }

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm(
            'SELECT id, area, nome, email, telefone, endereco, horario, competencias FROM contatos WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    /** @param array{nome: string, email: ?string, telefone: ?string, endereco: ?string, horario: ?string, competencias: ?string} $dados */
    public function atualizar(int $id, array $dados): void
    {
        $this->executar(
            'UPDATE contatos
             SET nome = :nome, email = :email, telefone = :telefone, endereco = :endereco,
                 horario = :horario, competencias = :competencias
             WHERE id = :id',
            $dados + ['id' => $id]
        );
    }
}
