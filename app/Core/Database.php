<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/** Conexão única (por requisição) com o MySQL via PDO. */
final class Database
{
    private static ?PDO $conexao = null;

    public static function conectar(): PDO
    {
        if (self::$conexao !== null) {
            return self::$conexao;
        }

        $config = require BASE_PATH . '/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['database']
        );

        try {
            $conexao = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Mantém datas do banco e do PHP no mesmo fuso (Manaus não tem horário de verão).
            $conexao->exec("SET time_zone = '" . date('P') . "'");
        } catch (PDOException $e) {
            // A mensagem original fica no log. A exceção lançada não carrega host nem usuário.
            Logger::error('Falha na conexão com o banco de dados', ['detalhe' => $e->getMessage()]);

            throw new RuntimeException('Não foi possível conectar ao banco de dados.');
        }

        self::$conexao = $conexao;

        return self::$conexao;
    }
}
