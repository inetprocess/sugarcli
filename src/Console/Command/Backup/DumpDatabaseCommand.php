<?php

namespace SugarCli\Console\Command\Backup;

use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\ProcessUtils;

class DumpDatabaseCommand extends AbstractConfigOptionCommand
{
    protected static $compression_formats = array(
        'gzip' => '.gz',
        'bzip2' => '.bz2',
    );

    protected function configure()
    {
        $compression_values = implode('|', array_keys(self::$compression_formats));
        $this->setName('backup:dump:database')
            ->setDescription('Create a backup archive of SugarCRM files.')
            ->enableStandardOption('path')
            ->addOption(
                'destination-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Destination folder for the achive',
                getenv('HOME') . '/backup'
            )
            ->addConfigOption(
                'backup.prefix',
                'prefix',
                'P',
                InputOption::VALUE_REQUIRED,
                'Prepend to the archive name',
                null,
                true
            )
            ->addOption(
                'compression',
                'c',
                InputOption::VALUE_REQUIRED,
                "Set the compression algorithm. Valid values are ({$compression_values}).",
                'gzip'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Do not run the command only print the tar command'
            )
            ;
    }

    protected function writeConfigFile($config_file, $dbconfig)
    {
        if ($dbconfig['db_type'] != 'mysql') {
            throw new \InvalidArgumentException("Database of type '{$dbconfig['db_type']}' is not supported");
        }
        $conf[] = "[mysqldump]";
        $params = array(
            'db_user_name' => 'user',
            'db_password' => 'password',
            'db_host_name' => 'host',
            'db_port' => 'port',
        );
        foreach ($params as $sugar_param => $mysql_param) {
            if (!empty($dbconfig[$sugar_param])) {
                $conf[] = implode("=", array($mysql_param, $dbconfig[$sugar_param]));
            }
        }
        file_put_contents($config_file, implode("\n", $conf));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check compression arg
        $compression = $input->getOption('compression');
        if (!array_key_exists($compression, self::$compression_formats)) {
            throw new \InvalidArgumentException("Invalid compression format '{$compression}'.");
        }
        // Check sugar path
        $sugar_app = $this->getService('sugarcrm.application');
        $sugar_path = $input->getOption('path');
        if (!$sugar_app->isValid()) {
            $output->writeln("<error>No SugarCRM instance found in '{$sugar_path}'.</error>");
            return ExitCode::EXIT_NOT_EXTRACTED;
        }

        $config_h = tmpfile();
        $meta_data = stream_get_meta_data($config_h);
        $config_file = $meta_data['uri'];

        $sugar_config = $sugar_app->getSugarConfig();
        $dbconfig = $sugar_config['dbconfig'];

        $dump_name = $input->getOption('prefix') . '_'
            . gethostname() . '@'
            . date('Y-m-d_H-i-s')
            . '.sql' . self::$compression_formats[$compression];
        $dump_path = $input->getOption('destination-dir');
        $dump_fullpath = $dump_path . '/' . $dump_name;

        $mysqldump_args = array(
            'mysqldump',
            "--defaults-file=$config_file",
            '--events',
            '--routines',
            '--single-transaction',
            '--opt',
            '--force',
            $dbconfig['db_name'],
        );

        $mysqldump_proc = ProcessBuilder::create($mysqldump_args)->getProcess();
        // Append | gzip > dumpname
        $mysqldump_proc->setCommandLine(implode(' ', array(
            $mysqldump_proc->getCommandLine(),
            '|', ProcessUtils::escapeArgument($compression),
            '>', ProcessUtils::escapeArgument($dump_fullpath),
        )));

        $this->writeConfigFile($config_file, $dbconfig);

        // Execute mysqldump command
        if ($input->getOption('dry-run')) {
            // Print mysql command and exit
            $output->writeln($mysqldump_proc->getCommandLine());
            return ExitCode::EXIT_SUCCESS;
        }
        $helper = $this->getHelper('process');
        $helper->mustRun($output, $mysqldump_proc);
        $output->writeln("SugarCRM database backed up in file '$dump_fullpath'");
    }
}
