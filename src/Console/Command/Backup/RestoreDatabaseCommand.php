<?php

namespace SugarCli\Console\Command\Backup;

use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;
use SugarCli\Utils\Utils;
use SugarCli\Console\Command\Backup\Common;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

class RestoreDatabaseCommand extends AbstractConfigOptionCommand
{
    protected static $compression_formats = array(
        'gzip' => array(
            'gz',
        ),
        'bzip2' => array(
            'bz2',
        ),
    );

    protected $temp_file;

    protected function configure()
    {
        Common::addCommonRestoreOptions($this, self::$compression_formats);
        $this->setName('backup:restore:database')
            ->setDescription('Restore a database from a previous backup')
            ->addOption(
                'archive',
                'a',
                InputOption::VALUE_REQUIRED,
                'Dump file to extract'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force import even errors are encountered'
            )
            ->addOption(
                'no-skip-definer',
                null,
                InputOption::VALUE_NONE,
                'Do not remove the DEFINER attribute from sql dump'
            )
            ;
    }

    protected function buildMysqlCommand(InputInterface $input)
    {
        // Get SugarCRM Config
        $sugar_app = $this->getService('sugarcrm.application');
        $this->temp_file = Utils::createTempMySQLDefaultFileFromSugarConfig($sugar_app);
        $sugar_config = $sugar_app->getSugarConfig();
        $db_name = $sugar_config['dbconfig']['db_name'];

        $mysql_args = array(
            'mysql',
            '--defaults-file=' . $this->temp_file->getPathname(),
            '--default-character-set=utf8',
            '--one-database',
            $db_name,
        );
        if ($input->getOption('force')) {
            $mysql_args[] = '--force';
        }
        return ProcessBuilder::create($mysql_args)->getProcess();
    }

    protected function buildPipedCommands($input, $compression, $archive_path)
    {

        $cmd = ProcessBuilder::create(array(
            $compression,
            '--stdout',
            '--decompress',
            $archive_path,
        ))->getProcess()
          ->getCommandLine();

        if (!$input->getOption('no-skip-definer')) {
            $cmd .= ' | ' . Common::SED_CMD_REMOVE_DEFINER;
        }

        $mysql_proc = $this->buildMysqlCommand($input);
        $cmd .= ' | ' . $mysql_proc->getCommandLine();
        $mysql_proc->setCommandLine($cmd);
        return $mysql_proc;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $archive_path = $input->getOption('archive');
        if (!$fs->exists($archive_path)) {
            throw new \InvalidArgumentException('Archive file "' . $archive_path . '" not found');
        }

        $compression = Common::getCompression($input, $archive_path, self::$compression_formats);

        $mysql_proc = $this->buildPipedCommands($input, $compression, $archive_path);

        // Execute mysql command
        if ($input->getOption('dry-run')) {
            // Print tar command and exit
            $output->writeln($mysql_proc->getCommandLine());
            return ExitCode::EXIT_SUCCESS;
        }
        // Run in bash to have the pipefail error
        $mysql_proc->setInput($mysql_proc->getCommandLine());
        $mysql_proc->setCommandLine('/bin/bash -o pipefail -o xtrace');
        $mysql_proc->setTimeout(0);
        $helper = $this->getHelper('process');
        $helper->mustRun($output, $mysql_proc);
        $output->writeln("SugarCRM data loaded into database");
    }
}
