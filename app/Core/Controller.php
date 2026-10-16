<?php

declare(strict_types=1);

namespace App\Core;

use App\Helpers\Auth;
use App\Helpers\FlashMessage;
use App\Models\Auditoria;

/** Base dos controllers: renderização, redirecionamento, acesso e leitura segura de entrada. */
abstract class Controller
{
    protected function render(string $view, array $dados = [], string $layout = 'app'): void
    {
        echo View::renderizar($view, $dados, $layout);
    }

    /** Redireciona para um caminho interno da aplicação (nunca para endereço externo). */
    protected function redirecionar(string $caminho, array $query = []): void
    {
        header('Location: ' . url($caminho, $query));
        exit;
    }

    /**
     * Exige usuário autenticado e ativo.
     * Quem ainda precisa trocar a senha temporária só acessa a tela de troca.
     *
     * @return array<string, mixed>
     */
    protected function exigirLogin(bool $permitirTrocaSenha = false): array
    {
        $usuario = Auth::usuario();

        if ($usuario === null) {
            $this->redirecionar('/login');
        }

        if (!$permitirTrocaSenha && (int) $usuario['deve_trocar_senha'] === 1) {
            FlashMessage::aviso('Defina uma nova senha para continuar.');
            $this->redirecionar('/conta/senha');
        }

        return $usuario;
    }

    /**
     * Exige um perfil específico. A verificação é feita no servidor em toda requisição.
     *
     * @return array<string, mixed>
     */
    protected function exigirPerfil(string $perfil): array
    {
        $usuario = $this->exigirLogin();

        if ($usuario['perfil'] !== $perfil) {
            throw new HttpException(403, 'Perfil sem permissão para esta rota.');
        }

        return $usuario;
    }

    protected function get(string $campo): string
    {
        $valor = $_GET[$campo] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }

    protected function post(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }

    /** Lê um identificador numérico positivo da query string. Devolve 0 se for inválido. */
    protected function idGet(string $campo = 'id'): int
    {
        return $this->inteiroPositivo($_GET[$campo] ?? null);
    }

    /** Lê um identificador numérico positivo do corpo do formulário. Devolve 0 se for inválido. */
    protected function idPost(string $campo = 'id'): int
    {
        return $this->inteiroPositivo($_POST[$campo] ?? null);
    }

    protected function pagina(): int
    {
        return max(1, $this->inteiroPositivo($_GET['pagina'] ?? null));
    }

    protected function auditar(string $acao, ?string $entidade = null, ?int $entidadeId = null, ?string $descricao = null): void
    {
        $usuario = Auth::usuario();

        (new Auditoria())->registrar(
            $usuario !== null ? (int) $usuario['id'] : null,
            $acao,
            $entidade,
            $entidadeId,
            $descricao
        );
    }

    private function inteiroPositivo(mixed $valor): int
    {
        $inteiro = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $inteiro === false ? 0 : $inteiro;
    }
}
