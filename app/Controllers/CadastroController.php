<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Logger;
use App\Helpers\Auth;
use App\Helpers\Cpf;
use App\Helpers\FlashMessage;
use App\Helpers\Validator;
use App\Models\Auditoria;
use App\Models\OrgaoUnidade;
use App\Models\Usuario;
use PDOException;
use RuntimeException;

/**
 * Autocadastro do gestor patrimonial.
 * Por padrão o acesso é liberado na hora. Com CADASTRO_REQUER_APROVACAO=true o cadastro fica
 * pendente até a aprovação de um administrador.
 */
final class CadastroController extends Controller
{
    public function mostrar(): void
    {
        $this->redirecionarSeLogado();
        $this->renderizarFormulario(['nome' => '', 'matricula' => '', 'cpf' => '', 'orgao_id' => '', 'setor' => '', 'email' => '', 'telefone' => '']);
    }

    public function salvar(): void
    {
        $this->redirecionarSeLogado();

        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $auditoria = new Auditoria();

        // Campo-armadilha: pessoas não o veem, robôs costumam preenchê-lo. Finge sucesso e não grava nada.
        if ($this->post('website') !== '') {
            FlashMessage::sucesso('Cadastro recebido.');
            $this->redirecionar('/login');
        }

        $valores = [
            'nome' => $this->post('nome'),
            'matricula' => $this->post('matricula'),
            'cpf' => $this->post('cpf'),
            'orgao_id' => $this->post('orgao_id'),
            'setor' => $this->post('setor'),
            'email' => mb_strtolower($this->post('email')),
            'telefone' => $this->post('telefone'),
        ];

        if ($auditoria->contarPorIp('cadastro.solicitado', $ip, 60) >= (int) Config::obter('cadastro_max_por_hora_ip', 20)) {
            FlashMessage::erro('Foram feitas muitas solicitações de cadastro a partir desta rede. Tente novamente mais tarde.');
            $this->renderizarFormulario($valores, [], 429);

            return;
        }

        $senha = (string) ($_POST['senha'] ?? '');
        $dados = $valores + ['senha' => $senha, 'senha_confirmacao' => (string) ($_POST['senha_confirmacao'] ?? '')];

        $validador = (new Validator($dados))
            ->obrigatorio('nome', 'o nome completo')->tamanho('nome', 'o nome', 3, 150)
            ->tamanho('setor', 'o setor', 2, 120)->obrigatorio('setor', 'o setor')
            ->obrigatorio('email', 'o e-mail')->tamanho('email', 'o e-mail', 5, 190)->email('email')
            ->obrigatorio('telefone', 'o telefone')
            ->obrigatorio('orgao_id', 'o órgão')
            ->senha('senha')->confirmacao('senha', 'senha_confirmacao');

        if (!str_contains(trim($valores['nome']), ' ')) {
            $validador->adicionarErro('nome', 'Informe o nome completo, com nome e sobrenome.');
        }

        if ($valores['matricula'] !== '' && preg_match('/^[A-Za-z0-9.\/-]{1,30}$/', $valores['matricula']) !== 1) {
            $validador->adicionarErro('matricula', 'A matrícula deve ter até 30 caracteres, apenas letras, números, ponto, barra ou hífen.');
        }

        $telefone = preg_replace('/\D/', '', $valores['telefone']) ?? '';

        if ($valores['telefone'] !== '' && (strlen($telefone) < 10 || strlen($telefone) > 11)) {
            $validador->adicionarErro('telefone', 'Informe o telefone com DDD (10 ou 11 dígitos).');
        }

        if (!Cpf::valido($valores['cpf'])) {
            $validador->adicionarErro('cpf', 'Informe um CPF válido.');
        }

        $orgaoId = filter_var($valores['orgao_id'], FILTER_VALIDATE_INT);

        if ($valores['orgao_id'] !== '' && ($orgaoId === false || (new OrgaoUnidade())->buscarAtivoPorId($orgaoId) === null)) {
            $validador->adicionarErro('orgao_id', 'Selecione um órgão válido.');
        }

        $modelo = new Usuario();
        $cpfHash = null;

        if (!$validador->falhou()) {
            try {
                $cpfHash = Cpf::hash($valores['cpf']);
            } catch (RuntimeException $e) {
                // APP_KEY ausente ou curta: falha de configuração, não de quem está se cadastrando.
                Logger::error('Autocadastro indisponível: ' . $e->getMessage());
                FlashMessage::erro('O cadastro está temporariamente indisponível. Procure a CPAT.');
                $this->renderizarFormulario($valores, [], 503);

                return;
            }

            if ($modelo->emailExiste($valores['email'])) {
                $validador->adicionarErro('email', 'Já existe um cadastro com este e-mail.');
            }

            if ($modelo->cpfExiste($cpfHash)) {
                $validador->adicionarErro('cpf', 'Já existe um cadastro com este CPF. Se for o seu, procure a CPAT.');
            }
        }

        if ($validador->falhou()) {
            $this->renderizarFormulario($valores, $validador->erros(), 422);

            return;
        }

        $exigeAprovacao = (bool) Config::obter('cadastro_requer_aprovacao', false);

        try {
            $login = $modelo->gerarUsuarioUnico($valores['nome']);

            $id = $modelo->criar([
                'nome' => $valores['nome'],
                'usuario' => $login,
                'email' => $valores['email'],
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'perfil' => 'gestor',
                'orgao_id' => (int) $orgaoId,
                'setor' => $valores['setor'],
                'cargo' => null,
                'matricula' => $valores['matricula'] === '' ? null : $valores['matricula'],
                'telefone' => $telefone,
                'cpf_hash' => $cpfHash,
                'situacao' => $exigeAprovacao ? 'pendente' : 'ativo',
                'deve_trocar_senha' => 0,
                'aprovado_em' => $exigeAprovacao ? null : date('Y-m-d H:i:s'),
            ]);
        } catch (PDOException $e) {
            // Duas solicitações simultâneas podem disputar o mesmo e-mail, CPF ou usuário.
            if ($e->getCode() === '23000') {
                FlashMessage::erro('Não foi possível concluir o cadastro. Verifique os dados e tente novamente.');
                $this->renderizarFormulario($valores, [], 409);

                return;
            }

            throw $e;
        }

        $auditoria->registrar(
            $id,
            'cadastro.solicitado',
            'usuarios',
            $id,
            $exigeAprovacao ? 'Solicitação de cadastro de gestor' : 'Autocadastro de gestor'
        );

        if ($exigeAprovacao) {
            FlashMessage::sucesso(
                'Cadastro enviado. Seu usuário de acesso será "' . $login . '". '
                . 'Aguarde a aprovação da CPAT. Você receberá um e-mail quando for aprovado.'
            );
            $this->redirecionar('/login');
        }

        // Acesso imediato: já entra no portal com a conta recém-criada.
        $modelo->registrarLoginComSucesso($id);
        Auth::entrar($id);

        FlashMessage::sucesso(
            'Cadastro concluído. Seu usuário de acesso é "' . $login . '". Anote-o: você vai usá-lo nos próximos acessos.'
        );
        $this->redirecionar('/gestor');
    }

    private function redirecionarSeLogado(): void
    {
        $usuario = Auth::usuario();

        if ($usuario !== null) {
            $this->redirecionar(inicio_do_perfil((string) $usuario['perfil']));
        }
    }

    private function renderizarFormulario(array $valores, array $erros = [], int $status = 200): void
    {
        http_response_code($status);

        $this->render('cadastro/index', [
            'titulo' => Config::obter('cadastro_requer_aprovacao', false) ? 'Solicitar cadastro' : 'Criar conta',
            'exigeAprovacao' => (bool) Config::obter('cadastro_requer_aprovacao', false),
            'valores' => $valores,
            'erros' => $erros,
            'orgaos' => (new OrgaoUnidade())->listarAtivos(),
            'larga' => true,
        ], 'auth');
    }
}
