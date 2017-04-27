<?php

namespace SugarCli\Console\Command\Backup;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;

class DumpFilesCommand extends AbstractConfigOptionCommand
{
    protected static $compression_formats = array(
        'gzip' => '.gz',
        'bzip2' => '.bz2',
    );

    protected function configure()
    {
        $compression_values = implode('|', array_keys(self::$compression_formats));
        $this->setName('backup:dump:files')
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
                'ignore-upload',
                'U',
                InputOption::VALUE_NONE,
                'Ignore files in upload/ folder and `*-restore`'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Do not run the command only print the tar command'
            )
            ->addOption(
                'ignore-cache',
                'C',
                InputOption::VALUE_NONE,
                'Ignore cache folder'
            );
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

        // Calculate various paths
        $sugar_pathinfo = pathinfo(realpath($sugar_path));
        $sugar_parent_dir = $sugar_pathinfo['dirname'];
        $sugar_basename = $sugar_pathinfo['basename'];
        $archive_name = $input->getOption('prefix') . '_' . date('Y-m-d_H-i')
            . '.tar' . self::$compression_formats[$compression];
        $archive_path = $input->getOption('destination-dir');
        $archive_fullpath = $archive_path . '/' . $archive_name;

        // Create tar command
        $tar_args = array(
            '--create',
            '--file=' . $archive_fullpath,
            '--directory=' . $sugar_parent_dir,
            '--' . $compression,
        );
        if ($input->getOption('ignore-cache')) {
            $tar_args = array_merge($tar_args, array(
                '--exclude',
                $sugar_basename . '/cache',
            ));
        }
        if ($input->getOption('ignore-upload')) {
            $tar_args = array_merge($tar_args, array(
                '--exclude',
                $sugar_basename . '/upload/????????-????-????-????-????????????*',
                '--exclude',
                '*-restore',
            ));
        }
        $tar_args[] = $sugar_basename;

        $tar_cmd_builder = new ProcessBuilder();
        $tar_cmd_builder->setPrefix('tar');
        $tar_cmd_builder->setArguments($tar_args);

        // Execute tar command
        if ($input->getOption('dry-run')) {
            // Print tar command and exit
            $output->writeln($tar_cmd_builder->getProcess()->getCommandLine());
            return 0;
        }
        $tar_cmd_builder->getProcess()->mustRun();
        $output->writeln("SugarCRM files backed up in archive '$archive_fullpath'");
    }
}
