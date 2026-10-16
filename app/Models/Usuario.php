<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Texto;

/** Usuários do portal (administradores e gestores patrimoniais). */
final class Usuario extends Model
{
    private const COLUNAS_PUBLICAS = '
        u.id, u.nome, u.usuario, u.email, u.perfil, u.situacao, u.orgao_id, u.setor, u.cargo,
        u.matricula, u.telefone, u.deve_trocar_senha, u.ultimo_acesso, u.criado_em, u.aprovado_em,
        o.sigla AS orgao_sigla, o.nome AS orgao_nome
    ';

    /**
     * Dados para autenticação, incluindo o hash da senha e o estado de bloqueio.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorUsuario(string $usuario): ?array
    {
        return $this->consultarUm(
            '
            SELECT id, nome, email, senha, perfil, situacao, deve_trocar_senha, tentativas_login,
                   (bloqueado_ate IS NOT NULL AND bloqueado_ate > NOW()) AS bloqueado,
                   (bloqueado_ate IS NOT NULL AND bloqueado_ate <= NOW()) AS bloqueio_expirado
            FROM usuarios
            WHERE usuario = :usuario
            LIMIT 1
            ',
            ['usuario' => $usuario]
        );
    }

    /**
     * Dados do usuário sem o hash da senha.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm(
            'SELECT ' . self::COLUNAS_PUBLICAS . '
             FROM usuarios u
             LEFT JOIN orgaos_unidades o ON o.id = u.orgao_id
             WHERE u.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function buscarHashSenha(int $id): ?string
    {
        $linha = $this->consultarUm('SELECT senha FROM usuarios WHERE id = :id LIMIT 1', ['id' => $id]);

        return $linha['senha'] ?? null;
    }

    /**
     * Listagem paginada com busca (nome, e-mail ou usuário) e filtro de situação.
     * Cadastros pendentes aparecem primeiro.
     *
     * @return array{itens: list<array<string, mixed>>, total: int}
     */
    public function listar(string $busca, string $situacao, int $pagina, int $porPagina): array
    {
        $condicoes = [];
        $parametros = [];

        if ($busca !== '') {
            $like = '%' . addcslashes($busca, '%_\\') . '%';
            $condicoes[] = '(u.nome LIKE :busca_nome OR u.email LIKE :busca_email OR u.usuario LIKE :busca_usuario)';
            $parametros += ['busca_nome' => $like, 'busca_email' => $like, 'busca_usuario' => $like];
        }

        if ($situacao !== '') {
            $condicoes[] = 'u.situacao = :situacao';
            $parametros['situacao'] = $situacao;
        }

        $filtro = $condicoes === [] ? '' : 'WHERE ' . implode(' AND ', $condicoes);

        $total = (int) ($this->consultarUm('SELECT COUNT(*) AS total FROM usuarios u ' . $filtro, $parametros)['total'] ?? 0);

        $parametros['limite'] = $porPagina;
        $parametros['deslocamento'] = max(0, ($pagina - 1) * $porPagina);

        $itens = $this->consultar(
            'SELECT ' . self::COLUNAS_PUBLICAS . '
             FROM usuarios u
             LEFT JOIN orgaos_unidades o ON o.id = u.orgao_id
             ' . $filtro . "
             ORDER BY u.situacao = 'pendente' DESC, u.nome
             LIMIT :limite OFFSET :deslocamento",
            $parametros
        );

        return ['itens' => $itens, 'total' => $total];
    }

    public function usuarioExiste(string $usuario): bool
    {
        return $this->consultarUm('SELECT id FROM usuarios WHERE usuario = :usuario LIMIT 1', ['usuario' => $usuario]) !== null;
    }

    /** Usa nome.sobrenome e, se já existir, acrescenta um número (joao.sena2, joao.sena3...). */
    public function gerarUsuarioUnico(string $nomeCompleto): string
    {
        $base = Texto::sugerirUsuario($nomeCompleto);
        $login = $base;

        for ($numero = 2; $this->usuarioExiste($login); $numero++) {
            $login = $base . $numero;
        }

        return $login;
    }

    public function emailExiste(string $email, ?int $ignorarId = null): bool
    {
        return $this->consultarUm(
            'SELECT id FROM usuarios WHERE email = :email AND id <> :ignorar LIMIT 1',
            ['email' => $email, 'ignorar' => $ignorarId ?? 0]
        ) !== null;
    }

    public function cpfExiste(string $cpfHash): bool
    {
        return $this->consultarUm('SELECT id FROM usuarios WHERE cpf_hash = :hash LIMIT 1', ['hash' => $cpfHash]) !== null;
    }

    /**
     * @param array{nome: string, usuario: string, email: string, senha: string, perfil: string, orgao_id: ?int,
     *              setor: ?string, cargo: ?string, matricula: ?string, telefone: ?string, cpf_hash: ?string,
     *              situacao: string, deve_trocar_senha: int, aprovado_em: ?string} $dados
     */
    public function criar(array $dados): int
    {
        $this->executar(
            'INSERT INTO usuarios
                (nome, usuario, email, senha, perfil, orgao_id, setor, cargo, matricula, telefone, cpf_hash,
                 situacao, deve_trocar_senha, aprovado_em)
             VALUES
                (:nome, :usuario, :email, :senha, :perfil, :orgao_id, :setor, :cargo, :matricula, :telefone, :cpf_hash,
                 :situacao, :deve_trocar_senha, :aprovado_em)',
            $dados
        );

        return $this->ultimoId();
    }

    /**
     * Atualização feita pelo administrador.
     *
     * @param array{nome: string, email: string, perfil: string, orgao_id: ?int, setor: ?string, cargo: ?string,
     *              matricula: ?string, telefone: ?string, situacao: string} $dados
     */
    public function atualizar(int $id, array $dados): void
    {
        $this->executar(
            'UPDATE usuarios
             SET nome = :nome, email = :email, perfil = :perfil, orgao_id = :orgao_id, setor = :setor,
                 cargo = :cargo, matricula = :matricula, telefone = :telefone, situacao = :situacao
             WHERE id = :id',
            $dados + ['id' => $id]
        );
    }

    /** Atualização feita pelo próprio usuário (somente dados de contato e lotação). */
    public function atualizarPerfil(int $id, string $email, ?string $telefone, ?string $setor, ?string $cargo): void
    {
        $this->executar(
            'UPDATE usuarios SET email = :email, telefone = :telefone, setor = :setor, cargo = :cargo WHERE id = :id',
            ['email' => $email, 'telefone' => $telefone, 'setor' => $setor, 'cargo' => $cargo, 'id' => $id]
        );
    }

    /** Aprova um cadastro pendente. Devolve false se já tiver sido analisado. */
    public function aprovar(int $id, int $aprovadoPor): bool
    {
        return $this->executar(
            "UPDATE usuarios SET situacao = 'ativo', aprovado_em = NOW(), aprovado_por = :admin
             WHERE id = :id AND situacao = 'pendente'",
            ['admin' => $aprovadoPor, 'id' => $id]
        ) > 0;
    }

    public function rejeitar(int $id, int $analisadoPor): bool
    {
        return $this->executar(
            "UPDATE usuarios SET situacao = 'rejeitado', aprovado_por = :admin
             WHERE id = :id AND situacao = 'pendente'",
            ['admin' => $analisadoPor, 'id' => $id]
        ) > 0;
    }

    public function atualizarSenha(int $id, string $hash, bool $deveTrocar): void
    {
        $this->executar(
            'UPDATE usuarios
             SET senha = :senha, deve_trocar_senha = :deve_trocar, tentativas_login = 0, bloqueado_ate = NULL
             WHERE id = :id',
            ['senha' => $hash, 'deve_trocar' => $deveTrocar ? 1 : 0, 'id' => $id]
        );
    }

    /**
     * Registra uma tentativa de login inválida e bloqueia ao atingir o limite.
     * No MySQL, as atribuições do UPDATE são avaliadas da esquerda para a direita:
     * por isso o bloqueio é calculado antes de incrementar o contador.
     */
    public function registrarFalhaLogin(int $id, int $maxTentativas, int $minutosBloqueio): void
    {
        $this->executar(
            'UPDATE usuarios
             SET bloqueado_ate = CASE
                     WHEN tentativas_login + 1 >= :max_tentativas THEN DATE_ADD(NOW(), INTERVAL :minutos MINUTE)
                     ELSE bloqueado_ate
                 END,
                 tentativas_login = tentativas_login + 1
             WHERE id = :id',
            ['max_tentativas' => $maxTentativas, 'minutos' => $minutosBloqueio, 'id' => $id]
        );
    }

    public function registrarLoginComSucesso(int $id): void
    {
        $this->executar(
            'UPDATE usuarios SET tentativas_login = 0, bloqueado_ate = NULL, ultimo_acesso = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public function limparBloqueio(int $id): void
    {
        $this->executar('UPDATE usuarios SET tentativas_login = 0, bloqueado_ate = NULL WHERE id = :id', ['id' => $id]);
    }

    /** @return array{total: int, ativos: int} */
    public function contarTodos(): array
    {
        $linha = $this->consultarUm(
            "SELECT COUNT(*) AS total, COALESCE(SUM(situacao = 'ativo'), 0) AS ativos FROM usuarios"
        );

        return ['total' => (int) ($linha['total'] ?? 0), 'ativos' => (int) ($linha['ativos'] ?? 0)];
    }

    public function contar(string $perfil, string $situacao): int
    {
        $linha = $this->consultarUm(
            'SELECT COUNT(*) AS total FROM usuarios WHERE perfil = :perfil AND situacao = :situacao',
            ['perfil' => $perfil, 'situacao' => $situacao]
        );

        return (int) ($linha['total'] ?? 0);
    }
}
