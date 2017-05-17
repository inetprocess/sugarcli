<?php

namespace SugarCli\Console\Command\Backup;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;
use SugarCli\Console\Command\Backup\Common;

class DumpFilesCommand extends AbstractConfigOptionCommand
{
    protected static $compression_formats = array(
        'gzip' => '.gz',
        'bzip2' => '.bz2',
    );

    protected static $du_bin = 'du';
    protected static $cache_dir_max_size = 200;
    protected static $upload_dir_max_size = 512;

    protected function configure()
    {
        Common::addCommonDumpOptions($this, self::$compression_formats);
        $this->setName('backup:dump:files')
            ->setDescription('Create a backup archive of SugarCRM files')
            ->addOption(
                'ignore-upload',
                'U',
                InputOption::VALUE_NONE,
                'Ignore files in upload/ folder and `*-restore`'
            )
            ->addOption(
                'ignore-cache',
                'C',
                InputOption::VALUE_NONE,
                'Ignore cache folder'
            );
    }

    public function warnForDirectorySize(InputInterface $input, OutputInterface $output, $sugar_path)
    {
        $warn = false;
        $logger = $this->getService('logger');
        $cache_path = $sugar_path . '/cache';
        $upload_path = $sugar_path . '/upload';
        $du_cmd = new ProcessBuilder(array(
            self::$du_bin,
            '--summarize',
            '--block-size=1M',
            $cache_path,
            $upload_path,
            $sugar_path,
        ));
        try {
            $helper = $this->getHelper('process');
            $du_output = $helper->mustRun($output, $du_cmd->getProcess())->getOutput();
        } catch (ProcessFailedException $e) {
            $logger->warning('Command `du` not available. Unable to test size of backup.');
            return;
        }
        preg_match_all('/^(\d+)\s+(\S.*)$/m', $du_output, $matches);
        foreach ($matches[1] as $idx => $size) {
            $sizes[$matches[2][$idx]] = (int) $size;
        }
        $total_size = $sizes[$sugar_path];
        // Warn if cache is over 200MB
        if (!$input->getOption('ignore-cache')) {
            if ($sizes[$cache_path] > self::$cache_dir_max_size) {
                $logger->warning(sprintf(
                    'Cache directory "%s" is huge with a size of %sMB. '
                    . 'You should consider ignoring this folder with `--ignore-cache`.',
                    $cache_path,
                    $sizes[$cache_path]
                ));
                $warn = true;
            }
            $total_size += $sizes[$cache_path];
        }
        // Warn if upload is over 512MB
        if (!$input->getOption('ignore-upload')) {
            if ($sizes[$upload_path] > self::$upload_dir_max_size) {
                $logger->warning(sprintf(
                    'Upload directory "%s" is huge with a size of %sMB. '
                    . 'You should consider ignoring this folder with `--ignore-upload`.',
                    $upload_path,
                    $sizes[$upload_path]
                ));
                $warn = true;
            }
            $total_size += $sizes[$upload_path];
        }
        $logger->notice(sprintf('Estimated size of %s MB of files to backup', $total_size));
        return $warn;
    }

    protected function buildTarProcess(InputInterface $input, $archive_fullpath)
    {
        // Calculate various paths
        $sugar_pathinfo = pathinfo(realpath($input->getOption('path')));
        $sugar_parent_dir = $sugar_pathinfo['dirname'];
        $sugar_basename = $sugar_pathinfo['basename'];

        // Create tar command
        $tar_args = array(
            'tar',
            '--create',
            '--file=' . $archive_fullpath,
            '--directory=' . $sugar_parent_dir,
            '--' . $input->getOption('compression'),
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

        return ProcessBuilder::create($tar_args)->getProcess();
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

        // Check sugar dir size
        if ($input->isInteractive()
            && !$input->getOption('no-interaction')
            && $this->warnForDirectorySize($input, $output, $sugar_path)) {
            $q_helper = $this->getHelper('question');
            $q = new ConfirmationQuestion('Dump files anyway ? [y/N]', false, '/^y/i');
            if (!$q_helper->ask($input, $output, $q)) {
                $output->writeln('Backup not run');
                return ExitCode::EXIT_DENIED_CONFIRMATION;
            }
        }

        $archive_name = $input->getOption('prefix') . '_'
            . gethostname() . '@'
            . date('Y-m-d_H-i-s')
            . '.tar' . self::$compression_formats[$compression];
        $archive_path = $input->getOption('destination-dir');
        $archive_fullpath = $archive_path . '/' . $archive_name;

        $tar_proc = $this->buildTarProcess($input, $archive_fullpath);

        // Execute tar command
        if ($input->getOption('dry-run')) {
            // Print tar command and exit
            $output->writeln($tar_proc->getCommandLine());
            return ExitCode::EXIT_SUCCESS;
        }
        $helper = $this->getHelper('process');
        $helper->mustRun($output, $tar_proc);
        $output->writeln("SugarCRM files backed up in archive '$archive_fullpath'");
    }
}
