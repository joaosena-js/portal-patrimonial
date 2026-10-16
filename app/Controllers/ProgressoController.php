<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashMessage;
use App\Services\ProgressoService;
use DomainException;

/** Registro de conclusão de módulos pelo gestor. */
final class ProgressoController extends Controller
{
    public function concluir(): void
    {
        $usuario = $this->exigirPerfil('gestor');
        $moduloId = $this->idPost('modulo_id');

        try {
            $resultado = (new ProgressoService())->concluirModulo((int) $usuario['id'], $moduloId);
        } catch (DomainException $e) {
            FlashMessage::erro($e->getMessage());
            $this->redirecionar('/gestor/trilhas');
        }

        $this->auditar('modulo.concluido', 'modulos', $moduloId, 'Módulo concluído');

        if ($resultado['trilha_concluida']) {
            $this->auditar('trilha.concluida', 'trilhas', $resultado['trilha_id'], 'Trilha concluída');
            FlashMessage::sucesso('Módulo concluído. Você concluiu todos os módulos da trilha.');
        } else {
            FlashMessage::sucesso('Módulo concluído.');
        }

        $this->redirecionar('/gestor/trilha', ['id' => $resultado['trilha_id']]);
    }
}
