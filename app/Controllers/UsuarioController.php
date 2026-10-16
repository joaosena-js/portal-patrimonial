<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\FlashMessage;
use App\Helpers\Rotulos;
use App\Helpers\Validator;
use App\Models\OrgaoUnidade;
use App\Models\Usuario;
use App\Services\Mailer;

/** Cadastro, aprovação e manutenção de usuários (somente administradores). */
final class UsuarioController extends Controller
{
    public function index(): void
    {
        $this->exigirPerfil('admin');

        $busca = mb_substr($this->get('busca'), 0, 100);
        $situacao = array_key_exists($this->get('situacao'), Rotulos::SITUACOES_USUARIO) ? $this->get('situacao') : '';
        $pagina = $this->pagina();
        $porPagina = (int) Config::obter('itens_por_pagina', 15);

        $resultado = (new Usuario())->listar($busca, $situacao, $pagina, $porPagina);

        $query = array_filter(['busca' => $busca, 'situacao' => $situacao], static fn (string $v): bool => $v !== '');

        $this->render('usuarios/index', [
            'titulo' => 'Usuários',
            'usuarios' => $resultado['itens'],
            'total' => $resultado['total'],
            'busca' => $busca,
            'situacao' => $situacao,
            'paginacao' => [
                'pagina' => $pagina,
                'total_paginas' => max(1, (int) ceil($resultado['total'] / $porPagina)),
                'caminho' => '/admin/usuarios',
                'query' => $query,
            ],
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormulario('usuarios/cadastrar', 'Novo usuário', [
            'nome' => '', 'email' => '', 'perfil' => 'gestor', 'orgao_id' => '', 'setor' => '',
            'cargo' => '', 'matricula' => '', 'telefone' => '', 'situacao' => 'ativo',
        ]);
    }

    public function store(): void
    {
        $admin = $this->exigirPerfil('admin');

        $valores = $this->valoresDoFormulario();
        $valores['situacao'] = 'ativo';
        $senha = (string) ($_POST['senha'] ?? '');

        $validador = $this->validar($valores, $senha, true, null);

        if ($validador->falhou()) {
            $this->renderizarFormulario('usuarios/cadastrar', 'Novo usuário', $valores, $validador->erros());

            return;
        }

        $modelo = new Usuario();

        $id = $modelo->criar([
            'nome' => $valores['nome'],
            'usuario' => $modelo->gerarUsuarioUnico($valores['nome']),
            'email' => $valores['email'],
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'perfil' => $valores['perfil'],
            'orgao_id' => $valores['orgao_id'] === '' ? null : (int) $valores['orgao_id'],
            'setor' => $this->nuloSeVazio($valores['setor']),
            'cargo' => $this->nuloSeVazio($valores['cargo']),
            'matricula' => $this->nuloSeVazio($valores['matricula']),
            'telefone' => $this->telefoneSomenteDigitos($valores['telefone']),
            'cpf_hash' => null,
            'situacao' => 'ativo',
            'deve_trocar_senha' => 1,
            'aprovado_em' => date('Y-m-d H:i:s'),
        ]);

        $criado = $modelo->buscarPorId($id);

        $this->auditar('usuario.criado', 'usuarios', $id, 'Usuário cadastrado: ' . $valores['nome']);
        FlashMessage::sucesso(
            'Usuário cadastrado. Login de acesso: ' . ($criado['usuario'] ?? '')
            . '. Ele deverá definir uma nova senha no primeiro acesso.'
        );
        $this->redirecionar('/admin/usuarios');
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $usuario = $this->localizar($this->idGet());

        $this->renderizarFormulario('usuarios/editar', 'Editar usuário', [
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'perfil' => $usuario['perfil'],
            'orgao_id' => (string) ($usuario['orgao_id'] ?? ''),
            'setor' => (string) ($usuario['setor'] ?? ''),
            'cargo' => (string) ($usuario['cargo'] ?? ''),
            'matricula' => (string) ($usuario['matricula'] ?? ''),
            'telefone' => (string) ($usuario['telefone'] ?? ''),
            'situacao' => $usuario['situacao'],
        ], [], $usuario);
    }

    public function update(): void
    {
        $admin = $this->exigirPerfil('admin');

        $usuario = $this->localizar($this->idPost());
        $id = (int) $usuario['id'];

        $valores = $this->valoresDoFormulario();
        $valores['situacao'] = $this->post('situacao');

        // O administrador não pode remover o próprio acesso nem rebaixar o próprio perfil.
        if ($id === (int) $admin['id']) {
            $valores['perfil'] = 'admin';
            $valores['situacao'] = 'ativo';
        }

        $novaSenha = (string) ($_POST['senha'] ?? '');
        $validador = $this->validar($valores, $novaSenha, false, $id);

        if ($validador->falhou()) {
            $this->renderizarFormulario('usuarios/editar', 'Editar usuário', $valores, $validador->erros(), $usuario);

            return;
        }

        $modelo = new Usuario();
        $modelo->atualizar($id, [
            'nome' => $valores['nome'],
            'email' => $valores['email'],
            'perfil' => $valores['perfil'],
            'orgao_id' => $valores['orgao_id'] === '' ? null : (int) $valores['orgao_id'],
            'setor' => $this->nuloSeVazio($valores['setor']),
            'cargo' => $this->nuloSeVazio($valores['cargo']),
            'matricula' => $this->nuloSeVazio($valores['matricula']),
            'telefone' => $this->telefoneSomenteDigitos($valores['telefone']),
            'situacao' => $valores['situacao'],
        ]);

        if ($novaSenha !== '') {
            $modelo->atualizarSenha($id, password_hash($novaSenha, PASSWORD_DEFAULT), true);
            $this->auditar('usuario.senha_redefinida', 'usuarios', $id, 'Senha temporária definida para ' . $valores['nome']);
        }

        $this->auditar('usuario.atualizado', 'usuarios', $id, 'Usuário atualizado: ' . $valores['nome']);
        FlashMessage::sucesso('Usuário atualizado.');
        $this->redirecionar('/admin/usuarios');
    }

    /** Aprova um cadastro pendente e avisa o usuário por e-mail. */
    public function aprovar(): void
    {
        $admin = $this->exigirPerfil('admin');

        $usuario = $this->localizar($this->idPost());
        $id = (int) $usuario['id'];

        if (!(new Usuario())->aprovar($id, (int) $admin['id'])) {
            FlashMessage::aviso('Este cadastro já foi analisado.');
            $this->redirecionar('/admin/usuarios');
        }

        $this->auditar('usuario.aprovado', 'usuarios', $id, 'Cadastro aprovado: ' . $usuario['nome']);

        $enviado = (new Mailer())->enviar(
            (string) $usuario['email'],
            'Cadastro aprovado - ' . (string) Config::obter('nome'),
            "Olá, {$usuario['nome']}.\n\n"
            . "Seu cadastro no " . (string) Config::obter('nome') . " foi aprovado.\n"
            . "Seu usuário de acesso é: {$usuario['usuario']}\n"
            . "Use a senha que você definiu no cadastro.\n\n"
            . 'Acesse: ' . url_absoluta('/login') . "\n\n"
            . "Coordenadoria de Patrimônio (CPAT) - SEAD"
        );

        FlashMessage::sucesso(
            'Cadastro aprovado.' . ($enviado ? ' O usuário foi avisado por e-mail.' : ' Não foi possível enviar o e-mail de aviso.')
        );
        $this->redirecionar('/admin/usuarios');
    }

    public function rejeitar(): void
    {
        $admin = $this->exigirPerfil('admin');

        $usuario = $this->localizar($this->idPost());
        $id = (int) $usuario['id'];

        if ((new Usuario())->rejeitar($id, (int) $admin['id'])) {
            $this->auditar('usuario.rejeitado', 'usuarios', $id, 'Cadastro rejeitado: ' . $usuario['nome']);
            FlashMessage::sucesso('Cadastro rejeitado.');
        } else {
            FlashMessage::aviso('Este cadastro já foi analisado.');
        }

        $this->redirecionar('/admin/usuarios');
    }

    /** @return array<string, mixed> */
    private function localizar(int $id): array
    {
        $usuario = $id > 0 ? (new Usuario())->buscarPorId($id) : null;

        if ($usuario === null) {
            throw new HttpException(404, 'Usuário não encontrado.');
        }

        return $usuario;
    }

    /** @return array<string, string> */
    private function valoresDoFormulario(): array
    {
        return [
            'nome' => $this->post('nome'),
            'email' => mb_strtolower($this->post('email')),
            'perfil' => $this->post('perfil'),
            'orgao_id' => $this->post('orgao_id'),
            'setor' => $this->post('setor'),
            'cargo' => $this->post('cargo'),
            'matricula' => $this->post('matricula'),
            'telefone' => $this->post('telefone'),
        ];
    }

    private function validar(array $valores, string $senha, bool $senhaObrigatoria, ?int $ignorarId): Validator
    {
        $validador = new Validator($valores + ['senha' => $senha]);

        $validador
            ->obrigatorio('nome', 'o nome')->tamanho('nome', 'o nome', 3, 150)
            ->obrigatorio('email', 'o e-mail')->tamanho('email', 'o e-mail', 5, 190)->email('email')
            ->opcao('perfil', 'um perfil', array_keys(Rotulos::PERFIS))
            ->opcao('situacao', 'uma situação', array_keys(Rotulos::SITUACOES_USUARIO))
            ->tamanho('setor', 'o setor', 2, 120)
            ->tamanho('cargo', 'o cargo', 2, 120);

        if ($valores['matricula'] !== '' && preg_match('/^[A-Za-z0-9.\/-]{1,30}$/', $valores['matricula']) !== 1) {
            $validador->adicionarErro('matricula', 'A matrícula deve ter até 30 caracteres, apenas letras, números, ponto, barra ou hífen.');
        }

        $telefone = preg_replace('/\D/', '', $valores['telefone']) ?? '';

        if ($valores['telefone'] !== '' && (strlen($telefone) < 10 || strlen($telefone) > 11)) {
            $validador->adicionarErro('telefone', 'Informe o telefone com DDD (10 ou 11 dígitos).');
        }

        if (!isset($validador->erros()['email']) && (new Usuario())->emailExiste($valores['email'], $ignorarId)) {
            $validador->adicionarErro('email', 'Já existe um usuário com este e-mail.');
        }

        // Gestor precisa estar vinculado a um órgão ou unidade ativo.
        if ($valores['perfil'] === 'gestor' && $valores['orgao_id'] === '') {
            $validador->adicionarErro('orgao_id', 'Selecione o órgão ou unidade do gestor.');
        } elseif ($valores['orgao_id'] !== '') {
            $orgaoId = filter_var($valores['orgao_id'], FILTER_VALIDATE_INT);

            if ($orgaoId === false || (new OrgaoUnidade())->buscarAtivoPorId($orgaoId) === null) {
                $validador->adicionarErro('orgao_id', 'Selecione um órgão ou unidade válido.');
            }
        }

        if ($senhaObrigatoria || $senha !== '') {
            $validador->senha('senha');

            if ($senhaObrigatoria && $senha === '') {
                $validador->adicionarErro('senha', 'Informe a senha temporária.');
            }
        }

        return $validador;
    }

    private function nuloSeVazio(string $valor): ?string
    {
        return $valor === '' ? null : $valor;
    }

    private function telefoneSomenteDigitos(string $valor): ?string
    {
        $digitos = preg_replace('/\D/', '', $valor) ?? '';

        return $digitos === '' ? null : $digitos;
    }

    private function renderizarFormulario(string $view, string $titulo, array $valores, array $erros = [], ?array $usuario = null): void
    {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render($view, [
            'titulo' => $titulo,
            'valores' => $valores,
            'erros' => $erros,
            'orgaos' => (new OrgaoUnidade())->listarAtivos(),
            'usuario' => $usuario,
        ]);
    }
}
