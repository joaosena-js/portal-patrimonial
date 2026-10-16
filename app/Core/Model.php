<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Base dos models. Todas as consultas usam prepared statements.
 *
 * Atenção: com prepares nativos, o mesmo parâmetro nomeado não pode aparecer
 * duas vezes na mesma consulta. Use nomes diferentes (ex.: :busca_nome e :busca_email).
 */
abstract class Model
{
    protected PDO $conexao;

    public function __construct()
    {
        $this->conexao = Database::conectar();
    }

    /** @return list<array<string, mixed>> */
    protected function consultar(string $sql, array $parametros = []): array
    {
        return $this->preparar($sql, $parametros)->fetchAll();
    }

    /** @return array<string, mixed>|null */
    protected function consultarUm(string $sql, array $parametros = []): ?array
    {
        $linha = $this->preparar($sql, $parametros)->fetch();

        return $linha === false ? null : $linha;
    }

    /** Executa INSERT, UPDATE ou DELETE e devolve o número de linhas afetadas. */
    protected function executar(string $sql, array $parametros = []): int
    {
        return $this->preparar($sql, $parametros)->rowCount();
    }

    protected function ultimoId(): int
    {
        return (int) $this->conexao->lastInsertId();
    }

    private function preparar(string $sql, array $parametros): PDOStatement
    {
        $comando = $this->conexao->prepare($sql);

        foreach ($parametros as $nome => $valor) {
            $tipo = match (true) {
                is_int($valor) => PDO::PARAM_INT,
                is_bool($valor) => PDO::PARAM_BOOL,
                $valor === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };

            $comando->bindValue(is_int($nome) ? $nome + 1 : ':' . ltrim($nome, ':'), $valor, $tipo);
        }

        $comando->execute();

        return $comando;
    }
}
