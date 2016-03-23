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

namespace SugarCli\Console\Command\Install;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use SugarCli\Console\ExitCode;

/**
 * Check command to verify that Sugar is present and installed.
 */
class GetConfigCommand extends Command
{
    protected function configure()
    {
        $this->setName('install:config:get')
            ->setDescription('Write a default config_si.php file in the current folder')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Write to this file instead of config_si.php.',
                'config_si.php'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Overwrite existing file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config_file = $input->getOption('config');
        $logger = $this->getApplication()->getContainer()->get('logger');
        $config_res = __DIR__ . '/../../../../res/config_si.php';

        $fsys = new Filesystem();
        if ($fsys->exists($config_file)) {
            $logger->debug("File $config_file already exists.");
            if ($input->getOption('force')) {
                $output->writeln("Overwriting file $config_file.");
                $fsys->copy($config_res, $config_file, true);
            } else {
                $output->writeln("Will not overwrite existing file $config_file.");

                return ExitCode::EXIT_FILE_ALREADY_EXISTS;
            }
        } else {
            $fsys->copy($config_res, $config_file);
        }
    }
}
