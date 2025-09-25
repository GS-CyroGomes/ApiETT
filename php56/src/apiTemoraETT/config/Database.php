<?php
namespace Config;

use App\Helpers\Helper;
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

        try {
            $this->connection->connect();
        } catch (\Doctrine\DBAL\Exception\DriverException $e) {
            die("Erro de conexão: ".$e->getMessage());
        }
    }

    private function getConnection()
    {
        return $this->connection;
    }
    
    public function select($tableAs = [], $columns = '*', $where = null, $bindParams = [])
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        
        $cols = is_array($columns) ? implode(', ', $columns) : $columns;
        $queryBuilder->select($cols);
        
        foreach ($tableAs as $table => $alias) {
            $queryBuilder->from($table, $alias);
        }

        if ($where) {
            $queryBuilder->where($where);
            foreach ($bindParams as $key => $value) {
                $queryBuilder->setParameter($key, $value);
            }
        }
    
        $stmt = $queryBuilder->execute();
        $result = $stmt->fetchAll();

        if (is_null($result)) { return null; }
        if (count($result) == 1) { return $result[0]; }
        if (count($result) > 1) { return $result; }
    }

    public function insert($table, $data)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->insert($table);
        foreach ($data as $column => $value) {
            $queryBuilder->setValue($column, ':' . $column);
            $queryBuilder->setParameter($column, $value);
        }
        $queryBuilder->execute();
        return $this->getConnection()->lastInsertId();
    }

    public function insertWithEncrypted($table, $data) 
    {
        if (!array_key_exists('password', $data)) {
            throw new \Exception("Password is required");
        }
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->insert($table);
        foreach ($data as $column => $value) {
            if ($column == 'password'){
                $queryBuilder->setValue('password', 'AES_ENCRYPT(:password, :aeskey)')->setParameter('password', $value)->setParameter('aeskey', getenv('AESKEY'));
            }else{
                $queryBuilder->setValue($column, ':' . $column);
                $queryBuilder->setParameter($column, $value);
            }
        }
        $queryBuilder->execute();
        return $this->getConnection()->lastInsertId();
    }
}