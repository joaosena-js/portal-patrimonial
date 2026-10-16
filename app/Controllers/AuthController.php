<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Logger;
use App\Helpers\Auth;
use App\Helpers\FlashMessage;
use App\Helpers\Validator;
use App\Models\Auditoria;
use App\Models\Usuario;

/** Login, logout e troca de senha. */
final class AuthController extends Controller
{
    private const MENSAGEM_FALHA = 'Não foi possível acessar. Verifique o usuário e a senha. '
        . 'Após várias tentativas, o acesso é bloqueado temporariamente.';

    public function mostrarLogin(): void
    {
        $usuario = Auth::usuario();

        if ($usuario !== null) {
            $this->redirecionar(inicio_do_perfil($usuario['perfil']));
        }

        $this->render('auth/login', ['titulo' => 'Acesso ao portal', 'usuario' => ''], 'auth');
    }

    public function login(): void
    {
        $login = mb_strtolower($this->post('usuario'));
        $senha = (string) ($_POST['senha'] ?? '');

        $modelo = new Usuario();
        $usuario = ($login !== '' && mb_strlen($login) <= 60) ? $modelo->buscarPorUsuario($login) : null;

        if ($usuario === null) {
            // Gasta o mesmo tempo de uma verificação real para não revelar se o usuário existe.
            password_hash($senha, PASSWORD_DEFAULT);
            $this->falharLogin($login, null);

            return;
        }

        $usuarioId = (int) $usuario['id'];

        // Bloqueio já vencido: zera o contador para não bloquear de novo na primeira falha.
        if ((int) $usuario['bloqueio_expirado'] === 1) {
            $modelo->limparBloqueio($usuarioId);
        }

        $bloqueado = (int) $usuario['bloqueado'] === 1;

        if (!password_verify($senha, (string) $usuario['senha'])) {
            if ($usuario['situacao'] === 'ativo' && !$bloqueado) {
                $modelo->registrarFalhaLogin(
                    $usuarioId,
                    (int) Config::obter('login_max_tentativas', 5),
                    (int) Config::obter('login_bloqueio_minutos', 15)
                );
            }

            $this->falharLogin($login, $usuarioId);

            return;
        }

        // A partir daqui a senha está correta, então é seguro informar a situação do cadastro.
        if ($bloqueado) {
            $this->falharLogin($login, $usuarioId);

            return;
        }

        if ($usuario['situacao'] === 'pendente') {
            $this->falharLogin($login, $usuarioId, 'Seu cadastro ainda aguarda a aprovação da CPAT. Você receberá um e-mail quando for aprovado.');

            return;
        }

        if ($usuario['situacao'] === 'rejeitado') {
            $this->falharLogin($login, $usuarioId, 'Seu cadastro não foi aprovado. Procure a CPAT para mais informações.');

            return;
        }

        if ($usuario['situacao'] !== 'ativo') {
            $this->falharLogin($login, $usuarioId);

            return;
        }

        // Atualiza o hash se o algoritmo padrão do PHP tiver evoluído.
        if (password_needs_rehash((string) $usuario['senha'], PASSWORD_DEFAULT)) {
            $modelo->atualizarSenha($usuarioId, password_hash($senha, PASSWORD_DEFAULT), (int) $usuario['deve_trocar_senha'] === 1);
        }

        $modelo->registrarLoginComSucesso($usuarioId);
        Auth::entrar($usuarioId);

        (new Auditoria())->registrar($usuarioId, 'login', 'usuarios', $usuarioId, 'Acesso ao portal');

        $this->redirecionar(inicio_do_perfil((string) $usuario['perfil']));
    }

    public function logout(): void
    {
        $usuario = Auth::usuario();

        if ($usuario !== null) {
            $this->auditar('logout', 'usuarios', (int) $usuario['id'], 'Saída do portal');
        }

        Auth::sair();
        FlashMessage::sucesso('Você saiu do portal.');
        $this->redirecionar('/login');
    }

    public function mostrarTrocaSenha(): void
    {
        $usuario = $this->exigirLogin(true);

        $this->render('auth/senha', [
            'titulo' => 'Alterar senha',
            'erros' => [],
            'obrigatoria' => (int) $usuario['deve_trocar_senha'] === 1,
        ]);
    }

    public function trocarSenha(): void
    {
        $usuario = $this->exigirLogin(true);
        $usuarioId = (int) $usuario['id'];
        $modelo = new Usuario();

        $dados = [
            'senha_atual' => (string) ($_POST['senha_atual'] ?? ''),
            'senha_nova' => (string) ($_POST['senha_nova'] ?? ''),
            'senha_confirmacao' => (string) ($_POST['senha_confirmacao'] ?? ''),
        ];

        $validador = new Validator($dados);
        $validador->senha('senha_nova')->confirmacao('senha_nova', 'senha_confirmacao');

        $hashAtual = $modelo->buscarHashSenha($usuarioId) ?? '';

        if (!password_verify($dados['senha_atual'], $hashAtual)) {
            $validador->adicionarErro('senha_atual', 'A senha atual não confere.');
        } elseif ($dados['senha_nova'] === $dados['senha_atual']) {
            $validador->adicionarErro('senha_nova', 'A nova senha deve ser diferente da atual.');
        }

        if ($validador->falhou()) {
            http_response_code(422);
            $this->render('auth/senha', [
                'titulo' => 'Alterar senha',
                'erros' => $validador->erros(),
                'obrigatoria' => (int) $usuario['deve_trocar_senha'] === 1,
            ]);

            return;
        }

        $modelo->atualizarSenha($usuarioId, password_hash($dados['senha_nova'], PASSWORD_DEFAULT), false);
        $this->auditar('senha.alterada', 'usuarios', $usuarioId, 'Senha alterada pelo próprio usuário');

        FlashMessage::sucesso('Senha alterada com sucesso.');
        $this->redirecionar(inicio_do_perfil((string) $usuario['perfil']));
    }

    private function falharLogin(string $login, ?int $usuarioId, ?string $mensagem = null): void
    {
        // O usuário digitado não vai para o log, para não registrar dados pessoais sem necessidade.
        Logger::warning('Falha de login', [
            'usuario_id' => $usuarioId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        FlashMessage::erro($mensagem ?? self::MENSAGEM_FALHA);
        http_response_code(401);

        $this->render('auth/login', ['titulo' => 'Acesso ao portal', 'usuario' => $login], 'auth');
    }
}
