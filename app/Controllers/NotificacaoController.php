<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Models\Notificacao;

/** Central de novidades do usuário (sino) e adiamento do pop-up. */
final class NotificacaoController extends Controller
{
    public function index(): void
    {
        $usuario = $this->exigirLogin();
        $usuarioId = (int) $usuario['id'];
        $modelo = new Notificacao();

        $itens = $modelo->recentes($usuarioId, 50);

        // Abrir esta página conta como visualizar: as novidades listadas deixam de ser "novas" nos próximos acessos.
        $novas = array_map(
            static fn (array $item): int => (int) $item['id'],
            array_filter($itens, static fn (array $item): bool => (int) $item['nova'] === 1)
        );
        $modelo->marcarLidas($usuarioId, array_values($novas));

        $this->render('notificacoes/index', [
            'titulo' => 'Novidades',
            'itens' => $itens,
        ]);
    }

    /** "Lembrar depois": o pop-up não aparece de novo nesta sessão. As novidades continuam não lidas. */
    public function adiar(): void
    {
        $this->exigirLogin();

        Session::definir('novidades_adiadas', true);

        $voltar = $this->post('voltar');
        $destino = preg_match('#^/[A-Za-z0-9/_-]*$#', $voltar) === 1 ? $voltar : '/';

        $this->redirecionar($destino);
    }
}
