<?php

declare(strict_types=1);

/**
 * Cria o primeiro administrador do portal (uso único, em linha de comando).
 *
 *   php database/criar_admin.php
 *
 * A senha é digitada no terminal e não fica gravada em arquivo nem no histórico de comandos.
 */

use App\Helpers\Validator;
use App\Models\Usuario;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script só pode ser executado pela linha de comando.');
}

require dirname(__DIR__) . '/app/bootstrap.php';

function perguntar(string $rotulo): string
{
    echo $rotulo . ': ';

    return trim((string) fgets(STDIN));
}

function falhar(string $mensagem): void
{
    fwrite(STDERR, $mensagem . PHP_EOL);
    exit(1);
}

echo 'Criação do primeiro administrador do Portal de Capacitação Patrimonial' . PHP_EOL . PHP_EOL;

$nome = perguntar('Nome completo');
$email = mb_strtolower(perguntar('E-mail'));

echo 'Atenção: a senha digitada aparecerá na tela.' . PHP_EOL;
$senha = perguntar('Senha (mínimo 8 caracteres, com letra e número)');

if (mb_strlen($nome) < 3) {
    falhar('Nome inválido.');
}

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    falhar('E-mail inválido.');
}

$erroSenha = Validator::erroDeSenha($senha);

if ($erroSenha !== null) {
    falhar($erroSenha);
}

try {
    $modelo = new Usuario();

    if ($modelo->emailExiste($email)) {
        falhar('Já existe um usuário com este e-mail.');
    }

    $login = $modelo->gerarUsuarioUnico($nome);

    $id = $modelo->criar([
        'nome' => $nome,
        'usuario' => $login,
        'email' => $email,
        'senha' => password_hash($senha, PASSWORD_DEFAULT),
        'perfil' => 'admin',
        'orgao_id' => null,
        'setor' => null,
        'cargo' => null,
        'matricula' => null,
        'telefone' => null,
        'cpf_hash' => null,
        'situacao' => 'ativo',
        'deve_trocar_senha' => 0,
        'aprovado_em' => date('Y-m-d H:i:s'),
    ]);
} catch (Throwable $e) {
    falhar('Não foi possível criar o administrador. Verifique o .env e se o banco foi criado. ' . $e->getMessage());
}

echo PHP_EOL . 'Administrador criado (id ' . $id . ').' . PHP_EOL;
echo 'Usuário de acesso: ' . $login . PHP_EOL;
echo 'Acesse o portal e entre com esse usuário e a senha informada.' . PHP_EOL;
