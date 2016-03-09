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

namespace SugarCli\Console\Command\Anonymize;

use SugarCli\Console\Command\AbstractConfigOptionCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnonymizeRunCommand extends AbstractConfigOptionCommand
{

    protected function configure()
    {
        $this->setName('anonymize:run')
            ->setDescription('Run the Anonymizer')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the configuration file',
                'anonymization.yml'
            )->addOption(
                'pretend',
                null,
                InputOption::VALUE_NONE,
                "Don't run the queries"
            )->addOption(
                'sql',
                null,
                InputOption::VALUE_NONE,
                'Display the SQL'
            )->addOption(
                'table',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Anonymize only that table (repeat for multiple values)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new \Symfony\Component\Stopwatch\Stopwatch();
        $stopwatch->start('Anon');

        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $pdo = $this->getService('sugarcrm.pdo');

        // Anon READER
        $reader = new \Inet\Neuralyzer\Configuration\Reader($input->getOption('file'));

        // Now work on the DB
        $anon = new \Inet\Neuralyzer\Anonymizer\DB($pdo);
        $anon->setConfiguration($reader);

        // Get tables
        $tables = $input->getOption('table');
        if (empty($tables)) {
            $tables = $reader->getEntities();
        }

        foreach ($tables as $table) {
            $result = $pdo->query("SELECT COUNT(1) FROM $table");
            $data = $result->fetchAll(\PDO::FETCH_COLUMN);
            $total = (int)$data[0];
            if ($total === 0) {
                $output->writeln("<info>$table is empty</info>" . PHP_EOL);
                continue;
            }

            $bar = new ProgressBar($output, $total);
            $output->writeln("<info>Anonymizing $table</info>");
            $queries = $anon->processEntity($table, function () use ($bar) {
                $bar->advance();
            }, $input->getOption('pretend'), $input->getOption('sql'));

            $output->writeln(PHP_EOL);

            if ($input->getOption('sql')) {
                $output->writeln('<comment>Queries:</comment>');
                $output->writeln(implode(PHP_EOL, $queries));
                $output->writeln(PHP_EOL);
            }
        }

        $data = $pdo->query("SHOW TABLES LIKE '%_audit'");
        foreach ($data as $row) {
            $table = $row[0];
            $output->writeln("<info>Emptying $table</info>");
            $pdo->query("TRUNCATE TABLE `$table`");
        }

        $db = $pdo->query('select database()')->fetchColumn();

        // Get memory and execution time information
        $event = $stopwatch->stop('Anon');
        $memory = round($event->getMemory() / 1024 / 1024, 2);
        $time = round($event->getDuration() / 1000, 2);
        $time = ($time > 180 ? round($time / 60, 2) . 'mins' : "$time sec");

        // Final message
        $output->writeln(PHP_EOL . "<comment>Done in $time (consuming {$memory}Mb). To export the db run: </comment>");
        $output->writeln(" mysqldump $db | bzip2 > $db." . date('Ymd-Hi') . ".sql.bz2");
    }
}
