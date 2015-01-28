<?php

namespace SugarCli\Sugar;

abstract class TestCase extends \PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;

    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo === null) {
                $dsn = 'mysql:';
                $params[] = 'host=' . (empty($GLOBALS['TEST_DB_HOST']) ? 'localhost' : $GLOBALS['TEST_DB_HOST']);
                if (!empty($GLOBALS['TEST_DB_PORT'])) {
                    $params[] = 'port=' . $GLOBALS['TEST_DB_PORT'];
                }
                $params[] = 'dbname=' . $GLOBALS['TEST_DB_NAME'];

                $dsn .= implode(';', $params);

                self::$pdo = new \PDO($dsn, $GLOBALS['TEST_DB_USER'], $GLOBALS['TEST_DB_PASSWORD']);
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['TEST_DB_NAME']);
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

