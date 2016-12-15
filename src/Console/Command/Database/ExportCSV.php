<?php

namespace SugarCli\Console\Command\Database;

use CSanquer\ColibriCsv\CsvWriter;
use SugarCli\Console\Command\AbstractContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ExportCSV extends AbstractContainerAwareCommand
{
    protected $db;
    protected $csv;
    protected $force = false;
    protected $options = array();

    protected function configure()
    {
        $this->setName('database:export:csv')
            ->setDescription('Export mysql tables as csv files.')
            ->addArgument(
                'database',
                InputArgument::REQUIRED,
                'Database to use for the export.'
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

    protected function connectMySQL(InputInterface $input)
    {
        $my_cnf = file_get_contents(getenv('HOME') . '/.my.cnf');
        $my_cnf = preg_replace('/#.*$/m', '', $my_cnf);
        $mysql_config = parse_ini_string($my_cnf, true);
        $dsn = 'mysql:charset=utf8;dbname=' . $input->getArgument('database');
        if (isset($mysql_config['client']['host'])) {
            $dsn .= ";host={$mysql_config['client']['host']}";
        }
        $this->db = new \PDO($dsn, $mysql_config['client']['user'], $mysql_config['client']['password']);
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
                "File '$filename' already exists, will not override unless --force is specified"
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
        $results = $this->db->query("SELECT * FROM `${table_name}`");
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
        $query = file_get_contents($query_file);
        if (empty($query)) {
            throw new \RuntimeException('Unable to read input file or file is empty.');
        }
        $results = $this->db->query($query);
        $this->exportQueryResult($results, $output_file);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->force = $input->getOption('force');
        $this->setCsvOptions($input);
        $this->connectMySQL($input);

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
