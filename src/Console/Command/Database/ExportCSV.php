<?php

namespace SugarCli\Console\Command\Database;

use CSanquer\ColibriCsv\CsvWriter;
use SugarCli\Console\Command\AbstractConfigOptionCommand as Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ExportCSV extends Command
{
    protected $db;
    protected $csv;
    protected $force = false;
    protected $options = array();

    protected function configure()
    {
        $this->setName('database:export:csv')
            ->setDescription('Export mysql tables as csv files')
            ->addArgument(
                'database',
                InputArgument::REQUIRED,
                'Database to use for the export.'
            )
            ->enableStandardOption('path')
            ->setRequiredOption('path', false)
            ->addOption(
                'no-sugar',
                null,
                InputOption::VALUE_NONE,
                'Do not use sugar database credentials'
            )
            ->addOption(
                'db-user',
                'u',
                InputOption::VALUE_REQUIRED,
                'Database user name.'
            )
            ->addOption(
                'db-password',
                'P',
                InputOption::VALUE_REQUIRED,
                'Database password.'
            )
            ->addOption(
                'db-dsn',
                'd',
                InputOption::VALUE_REQUIRED,
                'DSN string for usage by PDO. By default will try to fetch parameters from <info>~/.my.cnf</info>.'
            )
            ->addOption(
                'db-my-cnf',
                null,
                InputOption::VALUE_REQUIRED,
                'MySQL configuration file to read for database connexion'
            )
            ->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_REQUIRED,
                'CSV files will be exported to this directory as TABLE_NAME.csv.',
                '.'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Overwrite existing CSV files.'
            )
            ->addOption(
                'include',
                'i',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Export only the tables matching this pattern.'
            )
            ->addOption(
                'exclude',
                'e',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Exclude the tables matching this pattern. Overrides <info>table</info> parameter.'
            )
            ->addOption(
                'input-file',
                'I',
                InputOption::VALUE_REQUIRED,
                'Export the query read from this file instead of tables.'
            )
            ->addOption(
                'output-file',
                'O',
                InputOption::VALUE_REQUIRED,
                'When exporting a query, specify this fully qualified file name.'
            )
            ->addOption(
                'csv-option',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify option for csv export. '
                . "Ex: -c 'delimiter=,'"
            );
    }

    protected function getDBParameters(InputInterface $input)
    {
        $defaults = array(
            'dsn' => 'mysql:charset=utf8',
            'user' => null,
            'password' => null,
        );
        $my_cnf_path = getenv('HOME') . '/.my.cnf';
        if ($input->getOption('db-my-cnf')) {
            $my_cnf_path = $input->getOption('db-my-cnf');
        }
        if (is_file($my_cnf_path)) {
            $my_cnf = file_get_contents($my_cnf_path);
            $my_cnf = preg_replace('/#.*$/m', '', $my_cnf);
            $mysql_config = parse_ini_string($my_cnf, true);
            if (isset($mysql_config['client']['host'])) {
                $defaults['dsn'] .= ";host={$mysql_config['client']['host']}";
            }
            if (isset($mysql_config['client']['port'])) {
                $defaults['dsn'] .= ";port={$mysql_config['client']['port']}";
            }
            if (isset($mysql_config['client']['user'])) {
                $defaults['user'] = $mysql_config['client']['user'];
            }
            if (isset($mysql_config['client']['password'])) {
                $defaults['password'] = $mysql_config['client']['password'];
            }
        }
        if ($input->getOption('db-dsn')) {
            $defaults['dsn'] = $input->getOption('db-dsn');
        }
        if ($input->getOption('db-user')) {
            $defaults['user'] = $input->getOption('db-user');
        }
        if ($input->getOption('db-password')) {
            $defaults['password'] = $input->getOption('db-password');
        }
        if (substr($defaults['dsn'], -1) != ':') {
            $defaults['dsn'] .= ';';
        }
        $defaults['dsn'] .= 'dbname=' . $input->getArgument('database');
        return $defaults;
    }

    protected function connectDB(InputInterface $input)
    {
        $logger = $this->getService('logger');
        if (!$input->getOption('no-sugar') && $input->getOption('path')
            && $this->getService('sugarcrm.application')->isInstalled()
        ) {
            $logger->info(
                'Using db server from SugarCRM configuration in '
                . $input->getOption('path') . '.'
            );
            $this->db = $this->getService('sugarcrm.pdo');
        } else {
            $db_config = $this->getDBParameters($input);
            $logger->info('Connecting to the DB with the following parameters: ' .$db_config['dsn']);
            $this->db = new \PDO($db_config['dsn'], $db_config['user'], $db_config['password']);
        }
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    protected function listTables()
    {
        $tables = $this->db->query('SHOW TABLES');
        return $tables->fetchAll(\PDO::FETCH_COLUMN);
    }

    protected function openCsvWriter($filename)
    {
        if (!$this->force && file_exists($filename)) {
            throw new \RuntimeException(
                "File '$filename' already exists, will not override unless --force is specified."
            );
        }
        $csv = new CsvWriter($this->options);
        $csv->open($filename);
        return $csv;
    }

    protected function setCsvOptions(InputInterface $input)
    {
        $defaults = array(
            'encoding' => 'UTF-8',
            'first_row_header' => true,
            'enclosing_mode' => 'nonnumeric',
        );
        foreach ($input->getOption('csv-option') as $option) {
            list($key, $value) = explode('=', $option);
            $defaults[$key] = $value;
        }
        $this->options = $defaults;
    }

    protected function exportQueryResult(\PDOStatement $results, $filename)
    {
        $csv = $this->openCsvWriter($filename);
        while ($row = $results->fetch(\PDO::FETCH_ASSOC)) {
            $csv->writeRow($row);
        }
        $csv->close();
    }

    protected function exportTable($output_dir, $table_name)
    {
        $filename = $output_dir . '/' . $table_name . '.csv';
        $results = $this->db->query("SELECT * FROM " . $table_name);
        $this->exportQueryResult($results, $filename);
    }

    protected function getTablesToExport(InputInterface $input)
    {
        $export = $this->listTables();
        $include = $input->getOption('include');
        $exclude = $input->getOption('exclude');
        if (!empty($include)) {
            $export = array_filter($export, function ($table) use ($include) {
                foreach ($include as $pattern) {
                    if (fnmatch($pattern, $table)) {
                        return true;
                    }
                }
                return false;
            });
        }
        if (!empty($exclude)) {
            $export = array_filter($export, function ($table) use ($exclude) {
                foreach ($exclude as $pattern) {
                    if (fnmatch($pattern, $table)) {
                        return false;
                    }
                }
                return true;
            });
        }
        if (empty($export)) {
            throw new \RuntimeException('No tables to export.');
        }
        return $export;
    }

    protected function exportQueryFromFile($query_file, $output_file)
    {
        $query_file = ($query_file === '-') ? 'php://stdin' : $query_file;
        $query = @file_get_contents($query_file);
        if (empty($query)) {
            throw new \RuntimeException("Input file '$query_file' not found or empty.");
        }
        $results = $this->db->query($query);
        $this->exportQueryResult($results, $output_file);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->force = $input->getOption('force');
        $this->setCsvOptions($input);
        $this->connectDB($input);

        $input_file = $input->getOption('input-file');
        if (empty($input_file)) {
            $output_dir = $input->getOption('output-dir');
            foreach ($this->getTablesToExport($input) as $table) {
                $output->writeln("Exporting table '$table'.");
                $this->exportTable($output_dir, $table);
            }
        } else {
            $output_file = $input->getOption('output-file');
            $output->writeln("Exporting query to '$output_file'.");
            $this->exportQueryFromFile($input_file, $output_file);
        }
    }
}
