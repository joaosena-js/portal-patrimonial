<?php

class Usuario
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = Database::conectar();
    }

    public function buscarPorEmail(string $email): ?array
    {
        $sql = '
            SELECT id, nome, email, senha, perfil, situacao
            FROM usuarios
            WHERE email = :email
            LIMIT 1
        ';

        $comando = $this->conexao->prepare($sql);
        $comando->execute(['email' => $email]);

        $usuario = $comando->fetch();

        return $usuario ?: null;
    }
}