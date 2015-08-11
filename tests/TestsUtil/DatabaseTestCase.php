<?php

namespace SugarCli\Tests\TestsUtil;

abstract class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;

    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo === null) {
                $dsn = 'mysql:';
                $params[] = 'host=' . getenv('SUGARCLI_DB_HOST');
                $params[] = 'port=' . getenv('SUGARCLI_DB_PORT');
                $params[] = 'dbname=' . getenv('SUGARCLI_DB_NAME');

                $dsn .= implode(';', $params);

                self::$pdo = new \PDO($dsn, getenv('SUGARCLI_DB_USER'), getenv('SUGARCLI_DB_PASSWORD'));
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, getenv('SUGARCLI_DB_NAME'));
        }
        return $this->conn;
    }

    /**
     * Return an empty data set for test that require a db connexion but no data
     */
    public function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_CsvDataSet();
    }
}
