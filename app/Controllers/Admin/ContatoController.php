<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\FlashMessage;
use App\Helpers\Validator;
use App\Models\Contato;

/** Edição dos contatos da CPAT, do DBM e do DBI (somente administradores). */
final class ContatoController extends Controller
{
    public function index(): void
    {
        $this->exigirPerfil('admin');

        $this->render('admin/contatos/index', [
            'titulo' => 'Contatos',
            'contatos' => (new Contato())->listarTodos(),
        ]);
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $contato = $this->localizar($this->idGet());

        $this->renderizarFormulario([
            'nome' => $contato['nome'],
            'email' => (string) ($contato['email'] ?? ''),
            'telefone' => (string) ($contato['telefone'] ?? ''),
            'endereco' => (string) ($contato['endereco'] ?? ''),
            'horario' => (string) ($contato['horario'] ?? ''),
            'competencias' => (string) ($contato['competencias'] ?? ''),
        ], [], $contato);
    }

    public function update(): void
    {
        $this->exigirPerfil('admin');

        $contato = $this->localizar($this->idPost());
        $id = (int) $contato['id'];

        $valores = [
            'nome' => $this->post('nome'),
            'email' => mb_strtolower($this->post('email')),
            'telefone' => $this->post('telefone'),
            'endereco' => $this->post('endereco'),
            'horario' => $this->post('horario'),
            'competencias' => $this->post('competencias'),
        ];

        $validador = (new Validator($valores))
            ->obrigatorio('nome', 'o nome')->tamanho('nome', 'o nome', 3, 150)
            ->tamanho('email', 'o e-mail', 5, 190)->email('email')
            ->tamanho('telefone', 'o telefone', 4, 60)
            ->tamanho('endereco', 'o endereço', 5, 255)
            ->tamanho('horario', 'o horário', 3, 120)
            ->tamanho('competencias', 'as competências', 5, 1500);

        if ($validador->falhou()) {
            $this->renderizarFormulario($valores, $validador->erros(), $contato);

            return;
        }

        $nulo = static fn (string $v): ?string => $v === '' ? null : $v;

        (new Contato())->atualizar($id, [
            'nome' => $valores['nome'],
            'email' => $nulo($valores['email']),
            'telefone' => $nulo($valores['telefone']),
            'endereco' => $nulo($valores['endereco']),
            'horario' => $nulo($valores['horario']),
            'competencias' => $nulo($valores['competencias']),
        ]);

        $this->auditar('contato.atualizado', 'contatos', $id, 'Contato atualizado: ' . $valores['nome']);
        FlashMessage::sucesso('Contato atualizado.');
        $this->redirecionar('/admin/contatos');
    }

    /** @return array<string, mixed> */
    private function localizar(int $id): array
    {
        $contato = $id > 0 ? (new Contato())->buscarPorId($id) : null;

        if ($contato === null) {
            throw new HttpException(404, 'Contato não encontrado.');
        }

        return $contato;
    }

    private function renderizarFormulario(array $valores, array $erros, array $contato): void
    {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render('admin/contatos/form', [
            'titulo' => 'Editar contato',
            'valores' => $valores,
            'erros' => $erros,
            'contato' => $contato,
        ]);
    }
}
