<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Logger;
use App\Core\Model;
use Throwable;

/** Registro de atividades relevantes (quem fez, o quê e quando). */
final class Auditoria extends Model
{
    public function registrar(
        ?int $usuarioId,
        string $acao,
        ?string $entidade = null,
        ?int $entidadeId = null,
        ?string $descricao = null
    ): void {
        try {
            $this->executar(
                'INSERT INTO logs_atividade (usuario_id, acao, entidade, entidade_id, descricao, ip)
                 VALUES (:usuario_id, :acao, :entidade, :entidade_id, :descricao, :ip)',
                [
                    'usuario_id' => $usuarioId,
                    'acao' => $acao,
                    'entidade' => $entidade,
                    'entidade_id' => $entidadeId,
                    'descricao' => $descricao !== null ? mb_substr($descricao, 0, 255) : null,
                    'ip' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
                ]
            );
        } catch (Throwable $e) {
            // Falha na auditoria não deve derrubar a operação principal, mas precisa ficar visível no log.
            Logger::error('Falha ao registrar auditoria', ['acao' => $acao, 'erro' => $e->getMessage()]);
        }
    }

    /** Quantas vezes a ação foi registrada para o IP nos últimos minutos (limite de tentativas). */
    public function contarPorIp(string $acao, string $ip, int $minutos): int
    {
        $linha = $this->consultarUm(
            'SELECT COUNT(*) AS total FROM logs_atividade
             WHERE acao = :acao AND ip = :ip AND criado_em >= DATE_SUB(NOW(), INTERVAL :minutos MINUTE)',
            ['acao' => $acao, 'ip' => $ip, 'minutos' => $minutos]
        );

        return (int) ($linha['total'] ?? 0);
    }

    /** @return list<array<string, mixed>> */
    public function recentes(int $limite): array
    {
        return $this->consultar(
            'SELECT l.id, l.acao, l.descricao, l.criado_em, u.nome AS usuario
             FROM logs_atividade l
             LEFT JOIN usuarios u ON u.id = l.usuario_id
             ORDER BY l.id DESC
             LIMIT :limite',
            ['limite' => $limite]
        );
    }
}
