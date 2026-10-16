<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Inscricao;
use App\Models\Modulo;
use App\Models\Progresso;
use App\Models\Trilha;
use DomainException;
use Throwable;

/**
 * Regra de negócio da conclusão de módulos.
 * Mensagens das DomainException são próprias para exibir ao usuário.
 */
final class ProgressoService
{
    /**
     * Marca o módulo como concluído e, se todos os módulos ativos da trilha estiverem
     * concluídos, encerra a inscrição.
     *
     * @return array{trilha_id: int, trilha_concluida: bool}
     */
    public function concluirModulo(int $usuarioId, int $moduloId): array
    {
        $modulo = (new Modulo())->buscarPorId($moduloId);

        if ($modulo === null || (int) $modulo['ativo'] !== 1) {
            throw new DomainException('Módulo não encontrado.');
        }

        $trilhaId = (int) $modulo['trilha_id'];
        $trilha = (new Trilha())->buscarPorId($trilhaId);

        if ($trilha === null || $trilha['situacao'] !== 'publicada') {
            throw new DomainException('A trilha deste módulo não está disponível.');
        }

        if ((new Inscricao())->buscar($usuarioId, $trilhaId) === null) {
            throw new DomainException('Inscreva-se na trilha para registrar o progresso.');
        }

        $conexao = Database::conectar();
        $conexao->beginTransaction();

        try {
            (new Progresso())->registrar($usuarioId, $moduloId);

            $totalModulos = (new Modulo())->contarAtivos($trilhaId);
            $concluidos = count((new Progresso())->idsConcluidos($usuarioId, $trilhaId));
            $trilhaConcluida = $totalModulos > 0 && $concluidos >= $totalModulos;

            if ($trilhaConcluida) {
                (new Inscricao())->marcarConcluida($usuarioId, $trilhaId);
            }

            $conexao->commit();
        } catch (Throwable $e) {
            $conexao->rollBack();

            throw $e;
        }

        return ['trilha_id' => $trilhaId, 'trilha_concluida' => $trilhaConcluida];
    }
}
