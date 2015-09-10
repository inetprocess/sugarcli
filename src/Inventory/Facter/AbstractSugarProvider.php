<?php

namespace SugarCli\Inventory\Facter;

use PDO;
use Inet\SugarCRM\Application;

abstract class AbstractSugarProvider implements FacterInterface
{
    protected $sugarApp;
    protected $pdo;

    abstract public function getFacts();

    public function __construct(Application $sugarApp, PDO $pdo)
    {
        $this->sugarApp = $sugarApp;
        $this->pdo = $pdo;
    }

    public function getApplication()
    {
        return $this->sugarApp;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function queryOne($sql)
    {
        $value = null;
        $stmt = $this->getPdo()->query($sql);
        if ($stmt !== false) {
            $result = $stmt->fetchAll();
            if (!empty($result)) {
                $value = $result[0][0];
            }
        }
        return $value;
    }
}
