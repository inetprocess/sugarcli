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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Inet\SugarCRM\Installer;
use Inet\SugarCRM\Exception\InstallerException;
use SugarCli\Console\ExitCode;
use SugarCli\Console\Command\AbstractConfigOptionCommand;

/**
 * Check command to verify that Sugar is present and installed.
 */
class RunCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('install:run')
            ->setDescription('Extract and install SugarCRM.')
            ->enableStandardOption('path')
            ->addOption(
                'url',
                'u',
                InputOption::VALUE_REQUIRED,
                '<comment>[DEPRECATED]</comment> This option does nothing and is only kept for backward compatibility.'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force installer to remove target directory if present.'
            )
            ->addOption(
                'source',
                's',
                InputOption::VALUE_REQUIRED,
                'Path to SugarCRM installation package.',
                'sugar.zip'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'PHP file to use as configuration for the installation.',
                'config_si.php'
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        $config_si = $input->getOption('config');
        $installer = new Installer(
            $this->getService('sugarcrm.application'),
            $input->getOption('source'),
            $config_si
        );
        try {
            $installer->run($force);
            $output->writeln('Installation was sucessfully completed.');
        } catch (InstallerException $e) {
            $logger = $this->getService('logger');
            $logger->error('An error occured during the installation.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_INSTALL_ERROR;
        }
    }
}
