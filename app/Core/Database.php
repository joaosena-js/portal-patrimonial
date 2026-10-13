<?php

class Database
{
    private static ?PDO $conexao = null;

    public static function conectar(): PDO
    {
        if (self::$conexao === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['database']
            );

            self::$conexao = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$conexao;
    }
}