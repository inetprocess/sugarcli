<?php

namespace SugarCli\Inventory\Facter;

use PDO;
use Symfony\Component\Process\Process;
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

    public function getPath()
    {
        return $this->getApplication()->getPath();
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function queryOne(\PDOStatement $stmt)
    {
        $value = null;
        $stmt->execute();
        if ($stmt !== false) {
            $result = $stmt->fetchAll();
            if (!empty($result)) {
                $value = $result[0][0];
            }
        }

        return $value;
    }

    public function exec($cmd, $cwd = null)
    {
        $process = new Process($cmd, $cwd);
        $process->mustRun();

        return $process->getOutput();
    }
}
