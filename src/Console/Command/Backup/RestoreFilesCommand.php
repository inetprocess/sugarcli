<?php

namespace SugarCli\Console\Command\Backup;

use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;
use SugarCli\Console\Command\Backup\Common;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class RestoreFilesCommand extends AbstractConfigOptionCommand
{
    protected static $compression_formats = array(
        'gzip' => array(
            'gz',
            'tgz',
            'taz',
        ),
        'bzip2' => array(
            'bz2',
            'tz2',
            'tbz',
            'tbz2',
        ),
    );

    protected function configure()
    {
        Common::addCommonRestoreOptions($this, self::$compression_formats);
        $this->setName('backup:restore:files')
            ->setDescription('Restore files from a previous backup')
            ->addOption(
                'archive',
                'a',
                InputOption::VALUE_REQUIRED,
                'Dump file to extract'
            )
            ->addOption(
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'Overwrite files in place if it already exists.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $archive_path = $input->getOption('archive');
        if (!$fs->exists($archive_path)) {
            throw new \InvalidArgumentException('Archive file "' . $archive_path . '" not found');
        }

        $compression = Common::getCompression($input, $archive_path, self::$compression_formats);

        $sugar_path = $input->getOption('path');

        $tar_args = array(
            'tar',
            '--extract',
            '--strip-components=1',
            '--file=' . $archive_path,
            '--directory=' . $sugar_path,
            '--' . $compression,
        );
        $tar_proc = ProcessBuilder::create($tar_args)->getProcess();

        // Execute tar command
        if ($input->getOption('dry-run')) {
            // Print tar command and exit
            $output->writeln($tar_proc->getCommandLine());
            return ExitCode::EXIT_SUCCESS;
        }
        if ($fs->exists($sugar_path) && !$input->getOption('overwrite')) {
            $orig_path = rtrim($sugar_path, '/') . '.orig';
            $fs->rename($sugar_path, $orig_path);
            $output->writeln("Exiting files have been moved to '$orig_path'");
        }
        $fs->mkdir($sugar_path, 0750);
        $tar_proc->setTimeout(0);
        $helper = $this->getHelper('process');
        $helper->mustRun($output, $tar_proc);
        $output->writeln("SugarCRM files extracted to '$sugar_path'");
    }
}
