<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Vitaliy Volkiskiy
 * @copyright 2005-2020 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command\Database;

use SugarCli\Console\Command\AbstractConfigOptionCommand;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractDatabaseCommand extends AbstractConfigOptionCommand
{

    protected function getFieldsDifference(array $moduleNames = [])
    {
        $modulesDiffs = [];

        if (empty($moduleNames)) {
            $moduleNames = $this->getModulesNames();
        }

        foreach ($moduleNames as $moduleName) {
            if (empty($moduleName)) {
                continue;
            }

            $modulesDiffs[$moduleName] = $this->processModule($moduleName);
        }

        return $modulesDiffs;
    }

    protected function processModule(string $moduleName)
    {
        $inMeta = [];
        $inTable = [];

        $pdo = $this->getService('sugarcrm.pdo');
        $dbName = $this->getDb($pdo);
        $cstmName = strtolower($moduleName). '_cstm';

        $resMeta = $pdo->query("SELECT name FROM fields_meta_data WHERE deleted = 0 AND custom_module = '$moduleName'");
        $resTable = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = '$cstmName';");

        foreach ($resMeta as $row) {
            $inMeta[] = $row['name'];
        }

        foreach ($resTable as $row) {
            if (in_array($row['COLUMN_NAME'], ['id_c'])) {
                continue;
            }

            $inTable[] = $row['COLUMN_NAME'];
        }

        return array_diff($inTable, $inMeta);
    }

    protected function getModulesNames()
    {
        $pdo = $this->getService('sugarcrm.pdo');
        $db = $this->getDb($pdo);
        $data = $pdo->query(
            $sql = "SHOW TABLES WHERE `tables_in_{$db}` LIKE '%_cstm'"
        );

        if ($data === false) {
            throw new \PDOException("Can't run the query to get _cstm tables: " . PHP_EOL . $sql);
        }

        $tables = array();
        foreach ($data as $row) {
            $tables[] = substr($row[0], 0, -5);
        }

        return $tables;
    }

    /**
     * Get the current DB Name
     *
     * @param \PDO $pdo
     *
     * @return string
     */
    protected function getDb(\PDO $pdo)
    {
        return $pdo->query('SELECT DATABASE()')->fetchColumn();
    }

    protected function unsetIncorrectModules(OutputInterface $output, array $modules)
    {
        $filteredModules = [];
        $availableModules = $this->getModulesNames();
        foreach ($modules as $moduleName) {
            if (!empty($moduleName) && in_array($moduleName, $availableModules)) {
                $filteredModules[] = $moduleName;
            } else {
                $output->writeln("<error>$moduleName does not exist or there is no {$moduleName}_cstm table for this module</error>");
            }
        }

        return $filteredModules;
    }
}
