<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashMessage;
use App\Helpers\Validator;
use App\Models\Usuario;

/** "Meu perfil": o usuário consulta seus dados e atualiza contato e lotação. */
final class PerfilController extends Controller
{
    public function index(): void
    {
        $usuario = $this->exigirLogin();

        $this->renderizarFormulario($usuario, [
            'email' => (string) $usuario['email'],
            'telefone' => (string) ($usuario['telefone'] ?? ''),
            'setor' => (string) ($usuario['setor'] ?? ''),
            'cargo' => (string) ($usuario['cargo'] ?? ''),
        ]);
    }

    public function atualizar(): void
    {
        $usuario = $this->exigirLogin();
        $id = (int) $usuario['id'];

        $valores = [
            'email' => mb_strtolower($this->post('email')),
            'telefone' => $this->post('telefone'),
            'setor' => $this->post('setor'),
            'cargo' => $this->post('cargo'),
        ];

        $validador = (new Validator($valores))
            ->obrigatorio('email', 'o e-mail')->tamanho('email', 'o e-mail', 5, 190)->email('email')
            ->tamanho('setor', 'o setor', 2, 120)
            ->tamanho('cargo', 'o cargo', 2, 120);

        $telefone = preg_replace('/\D/', '', $valores['telefone']) ?? '';

        if ($valores['telefone'] !== '' && (strlen($telefone) < 10 || strlen($telefone) > 11)) {
            $validador->adicionarErro('telefone', 'Informe o telefone com DDD (10 ou 11 dígitos).');
        }

        $modelo = new Usuario();

        if (!$validador->falhou() && $modelo->emailExiste($valores['email'], $id)) {
            $validador->adicionarErro('email', 'Este e-mail já está em uso por outro usuário.');
        }

        if ($validador->falhou()) {
            http_response_code(422);
            $this->renderizarFormulario($usuario, $valores, $validador->erros());

            return;
        }

        $modelo->atualizarPerfil(
            $id,
            $valores['email'],
            $telefone === '' ? null : $telefone,
            $valores['setor'] === '' ? null : $valores['setor'],
            $valores['cargo'] === '' ? null : $valores['cargo']
        );

        $this->auditar('perfil.atualizado', 'usuarios', $id, 'Dados de contato atualizados pelo próprio usuário');
        FlashMessage::sucesso('Dados atualizados.');
        $this->redirecionar('/perfil');
    }

    private function renderizarFormulario(array $usuario, array $valores, array $erros = []): void
    {
        $this->render('perfil/index', [
            'titulo' => 'Meu perfil',
            'usuario' => $usuario,
            'valores' => $valores,
            'erros' => $erros,
        ]);
    }
}
