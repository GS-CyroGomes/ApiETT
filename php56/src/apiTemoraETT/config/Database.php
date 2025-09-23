<?php
namespace Config;

use Doctrine\DBAL\DriverManager;

class Database
{
    private $connection;

    public function __construct()
    {
        // Configuração de conexão lendo direto do ambiente do container
        $connectionParams = [
            'dbname'   => getenv('DB_NAME') ?: 'default_db',
            'user'     => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: 'root',
            'host'     => getenv('DB_HOST') ?: 'mysql',
            'port'     => getenv('DB_PORT') ?: 3306,
            'driver'   => getenv('DB_DRIVER') ?: 'pdo_mysql',
            'charset'  => 'utf8'
        ];

        $this->connection = DriverManager::getConnection($connectionParams);
    }

    public function getConnection()
    {
        return $this->connection;
    }
}