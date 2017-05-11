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
        $compression_values = implode('|', array_keys(self::$compression_formats));
        $this->setName('backup:restore:files')
            ->setDescription('Restore files from a previous backup')
            ->enableStandardOption('path')
            ->addOption(
                'source-dir',
                's',
                InputOption::VALUE_REQUIRED,
                'Source folder archives can be found',
                getenv('HOME') . '/backup'
            )
            ->addOption(
                'archive',
                'a',
                InputOption::VALUE_REQUIRED,
                'Dump file to extract'
            )
            ->addOption(
                'compression',
                'c',
                InputOption::VALUE_REQUIRED,
                "Set the compression algorithm. By default it is guessed from file extention.'
                . ' Valid values are ({$compression_values})."
            )
            ->addOption(
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'Overwrite files in place if it already exists.'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Do not run the command only print the tar command'
            );
    }

    public function guessCompression($extension)
    {
        foreach (self::$compression_formats as $format => $format_exts) {
            if (in_array($extension, $format_exts)) {
                return $format;
            }
        }
        return null;
    }

    public function getCompression(InputInterface $input, $archive_path)
    {
        // Check compression arg
        $compression = $input->getOption('compression');
        if ($compression == null) {
            $path_info = new \SplFileInfo($archive_path);
            $compression = $this->guessCompression($path_info->getExtension());
            if ($compression == null) {
                throw new \InvalidArgumentException(
                    "Could not guess compression. Please set the --compression option."
                );
            }
        } elseif (!array_key_exists($compression, self::$compression_formats)) {
            throw new \InvalidArgumentException("Invalid compression format '{$compression}'.");
        }
        return $compression;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $archive_path = $input->getOption('archive');
        if (!$fs->exists($archive_path)) {
            throw new \InvalidArgumentException('Archive file "' . $archive_path . '" not found');
        }

        $compression = $this->getCompression($input, $archive_path);

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
        $helper = $this->getHelper('process');
        $helper->mustRun($output, $tar_proc);
        $output->writeln("SugarCRM files extracted to '$sugar_path'");
    }
}
