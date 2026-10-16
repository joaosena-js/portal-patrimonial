<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashMessage;
use App\Helpers\Validator;
use App\Models\Aviso;
use App\Models\Notificacao;
use DateTimeImmutable;

/** Quadro de avisos: leitura por todos os perfis, gestão pelos administradores. */
final class AvisoController extends Controller
{
    public function index(): void
    {
        $usuario = $this->exigirLogin();
        $ehAdmin = $usuario['perfil'] === 'admin';
        $modelo = new Aviso();

        $this->render('avisos/index', [
            'titulo' => 'Quadro de avisos',
            'avisos' => $ehAdmin ? $modelo->listarTodos(100) : $modelo->listarVisiveis(50),
            'ehAdmin' => $ehAdmin,
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormulario(['titulo' => '', 'mensagem' => '', 'expira_em' => '', 'urgente' => '0']);
    }

    public function store(): void
    {
        $admin = $this->exigirPerfil('admin');

        $valores = [
            'titulo' => $this->post('titulo'),
            'mensagem' => $this->post('mensagem'),
            'expira_em' => $this->post('expira_em'),
            'urgente' => $this->post('urgente') === '1' ? '1' : '0',
        ];

        $validador = (new Validator($valores))
            ->obrigatorio('titulo', 'o título')->tamanho('titulo', 'o título', 3, 150)
            ->obrigatorio('mensagem', 'a mensagem')->tamanho('mensagem', 'a mensagem', 10, 2000);

        if ($valores['expira_em'] !== '') {
            $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valores['expira_em']);
            $valida = $data !== false && $data->format('Y-m-d') === $valores['expira_em'];

            if (!$valida) {
                $validador->adicionarErro('expira_em', 'Informe uma data de validade válida.');
            } elseif ($valores['expira_em'] < date('Y-m-d')) {
                $validador->adicionarErro('expira_em', 'A validade não pode ser anterior a hoje.');
            }
        }

        if ($validador->falhou()) {
            $this->renderizarFormulario($valores, $validador->erros());

            return;
        }

        $id = (new Aviso())->criar([
            'titulo' => $valores['titulo'],
            'mensagem' => $valores['mensagem'],
            'urgente' => (int) $valores['urgente'],
            'expira_em' => $valores['expira_em'] === '' ? null : $valores['expira_em'],
            'publicado_por' => (int) $admin['id'],
        ]);

        (new Notificacao())->criar('aviso', ($valores['urgente'] === '1' ? 'Aviso urgente: ' : 'Novo aviso: ') . $valores['titulo'], $id, $valores['urgente'] === '1');

        $this->auditar('aviso.criado', 'avisos', $id, 'Aviso publicado: ' . $valores['titulo']);
        FlashMessage::sucesso('Aviso publicado.');
        $this->redirecionar('/avisos');
    }

    public function desativar(): void
    {
        $this->exigirPerfil('admin');

        $id = $this->idPost();

        if ($id > 0 && (new Aviso())->desativar($id)) {
            $this->auditar('aviso.desativado', 'avisos', $id, 'Aviso retirado do quadro');
            FlashMessage::sucesso('Aviso retirado do quadro.');
        }

        $this->redirecionar('/avisos');
    }

    private function renderizarFormulario(array $valores, array $erros = []): void
    {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render('avisos/cadastrar', [
            'titulo' => 'Novo aviso',
            'valores' => $valores,
            'erros' => $erros,
        ]);
    }
}
