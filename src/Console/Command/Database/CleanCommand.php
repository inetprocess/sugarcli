<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command\Database;

use SugarCli\Console\Command\AbstractConfigOptionCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CleanCommand extends AbstractConfigOptionCommand
{

    protected function configure()
    {
        $this->setName('database:clean')
            ->setDescription('Remove deleted records as well as data in audit and lost records in _cstm tables')
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addOption(
                'remove-deleted',
                null,
                InputOption::VALUE_NONE,
                "Remove all records with deleted = 1. Won't be launched if --force is not set"
            )->addOption(
                'clean-cstm',
                null,
                InputOption::VALUE_NONE,
                "Clean all records in _cstm that are not in the main table. Won't be launched if --force is not set"
            )->addOption(
                'clean-history',
                null,
                InputOption::VALUE_NONE,
                "Clean *_audit, job_queue and trackers"
            )->addOption(
                'clean-activities',
                null,
                InputOption::VALUE_NONE,
                "Clean activities_* and trackers"
            )->addOption(
                'table',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Clean only that table (repeat for multiple values)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new \Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->start('DbClean');

        $pdo = $this->getService('sugarcrm.pdo');
        $this->getService('sugarcrm.entrypoint'); // go to sugar folder to make sure we are in the right folder

        if ($input->getOption('remove-deleted') === false && $input->getOption('clean-cstm') === false
          && $input->getOption('clean-history') === false && $input->getOption('clean-activities') === false) {
            $msg = 'You need to set at least --remove-deleted or --clean-xxxxxx';
            throw new \InvalidArgumentException($msg);
        }

        // Make sure that we don't anonymize production
        if ($this->askConfirmation($input, $output) === false) {
            return;
        }


        $tables = $input->getOption('table');
        if (empty($tables)) {
            $tables = $this->getNonAuditAndCacheTables($pdo);
        }
        foreach ($tables as $table) {
            if ($this->tableExists($pdo, $table) === false) {
                $output->writeln("<error>$table does not exist</error>");
                continue;
            }
            $this->cleanDeletedAndCustomTable($input, $output, $pdo, $table);
        }

        $tables = $input->getOption('table');
        $auditJobQueueAndTrackerTables = $this->getAuditJobQueueAndTrackersTables($input, $pdo);
        foreach ($auditJobQueueAndTrackerTables as $table) {
            if ((!empty($tables) && in_array($table, $tables)) || empty($tables)) {
                $pdo->query("TRUNCATE TABLE `$table`");
                $output->writeln("<info>Removed everything from $table</info>");
            }
        }

        $tables = $input->getOption('table');
        $activitiesTables = $this->getActivitiesTables($input, $pdo);
        foreach ($activitiesTables as $table) {
            if ((!empty($tables) && in_array($table, $tables)) || empty($tables)) {
                $pdo->query("TRUNCATE TABLE `$table`");
                $output->writeln("<info>Removed everything from $table</info>");
            }
        }


        // Get memory and execution time information
        $event = $stopwatch->stop('DbClean');
        $memory = round($event->getMemory() / 1024 / 1024, 2);
        $time = round($event->getDuration() / 1000, 2);
        $time = ($time > 180 ? round($time / 60, 2) . 'mins' : "$time sec");

        // Final message
        $output->writeln(PHP_EOL . "<comment>Done in $time (consuming {$memory}Mb)</comment>");
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

    /**
     * Check if the table exists
     * @param  PDO    $pdo
     * @param  string $table
     * @return bool
     */
    protected function tableExists(\PDO $pdo, $table)
    {
        $db = $this->getDb($pdo);
        $data = $pdo->query($sql = "SHOW TABLES WHERE `tables_in_{$db}` LIKE '{$table}'");
        if ($data === false) {
            throw new \PDOException("Can't run the query to get the table: " . PHP_EOL . $sql);
        }

        return $data->rowCount() === 1 ? true : false;
    }

    /**
     * Get the current DB Name
     *
     * @param \PDO $pdo
     *
     * @return string
     */
    protected function getNonAuditAndCacheTables(\PDO $pdo)
    {
        $db = $this->getDb($pdo);
        $data = $pdo->query(
            $sql = "SHOW TABLES WHERE `tables_in_{$db}`
                    NOT LIKE '%_audit' AND `tables_in_{$db}` NOT LIKE '%_cache'
                    AND `tables_in_{$db}` NOT LIKE 'job_queue'
                    AND `tables_in_{$db}` NOT LIKE 'tracker'
                    AND `tables_in_{$db}` NOT LIKE 'tracker_%'"
        );
        if ($data === false) {
            throw new \PDOException("Can't run the query to empty audit and trackers: " . PHP_EOL . $sql);
        }

        $tables = array();
        foreach ($data as $row) {
            $tables[] = $row[0];
        }

        return $tables;
    }


    /**
     * Get all tables %_audit, job_queue and tracker%
     *
     * @param InputInterface  $input
     * @param \PDO            $pdo
     */
    protected function getAuditJobQueueAndTrackersTables(InputInterface $input, \PDO $pdo)
    {
        if ($input->getOption('clean-history') === false) {
            return array();
        }

        $db = $this->getDb($pdo);
        $data = $pdo->query(
            $sql = "SHOW TABLES WHERE `tables_in_{$db}`
                    LIKE '%_audit' OR `tables_in_{$db}` LIKE '%_cache'
                    OR `tables_in_{$db}` LIKE 'job_queue'
                    OR `tables_in_{$db}` LIKE 'tracker'
                    OR `tables_in_{$db}` LIKE 'tracker_%'"
        );

        if ($data === false) {
            throw new \PDOException("Can't run the query to get audit, job queue and trackers: " . PHP_EOL . $sql);
        }
        $tables = array();
        foreach ($data as $row) {
            $tables[] = $row[0];
        }

        return $tables;
    }

    /**
     * Get all tables activities%
     *
     * @param InputInterface  $input
     * @param \PDO            $pdo
     */
    protected function getActivitiesTables(InputInterface $input, \PDO $pdo)
    {
        if ($input->getOption('clean-activities') === false) {
            return array();
        }

        $db = $this->getDb($pdo);
        $data = $pdo->query(
            $sql = "SHOW TABLES WHERE `tables_in_{$db}` LIKE 'activities%'"
        );

        if ($data === false) {
            throw new \PDOException("Can't run the query to empty activities tables: " . PHP_EOL . $sql);
        }
        $tables = array();
        foreach ($data as $row) {
            $tables[] = $row[0];
        }

        return $tables;
    }

    /**
     * Clean the current table : remove deleted and/or the records not in cstm
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param \PDO            $pdo
     * @param string          $table
     */
    protected function cleanDeletedAndCustomTable(InputInterface $input, OutputInterface $output, \PDO $pdo, $table)
    {
        // removing deleted from the tables which have that field
        if ($input->getOption('remove-deleted') === true) {
            $deletedField = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'deleted'")->fetchColumn();
            if (!empty($deletedField)) {
                $del = $pdo->query("DELETE FROM $table WHERE deleted = 1");
                if ($del === false) {
                    throw new \PDOException("Can't run the query to delete records from $table");
                }
                $output->writeln('<info>Removed ' . $del->rowCount() . " deleted records from $table</info>");
            }
        }

        // Clean custom table if asked, with id not in the main table
        if ($input->getOption('clean-cstm') === true && substr($table, -5) === '_cstm') {
            $del = $pdo->query(
                "DELETE FROM $table WHERE id_c NOT IN (SELECT id FROM `" . substr($table, 0, -5) . '`)'
            );
            if ($del === false) {
                throw new \PDOException("Can't run the query to delete records from $table");
            }
            $output->writeln('<info>Removed ' . $del->rowCount() . " useless records from $table</info>");
        }
    }

    /**
     * Ask a confirmation to force
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function askConfirmation(InputInterface $input, OutputInterface $output)
    {
        $msg = 'Be careful, the cleaning is going to start. That will remove data in the Database !';
        $output->writeln("<error>$msg</error>");
        $helper = $this->getHelper('question');
        $question = new Question('If you are sure, please type "yes" in uppercase' . PHP_EOL);
        $confirmation = $helper->ask($input, $output, $question);
        if ($confirmation !== 'YES') {
            $output->writeln('Bye !');

            return false;
        }
        $output->writeln('');
    }
}
