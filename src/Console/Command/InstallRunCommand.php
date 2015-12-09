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
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Inet\SugarCRM\Application;
use Inet\SugarCRM\Installer;
use Inet\SugarCRM\Exception\InstallerException;
use SugarCli\Console\ExitCode;

/**
 * Check command to verify that Sugar is present and installed.
 */
class InstallRunCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('install:run')
            ->setDescription('Extract and install SugarCRM.')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
            ->addConfigOptionMapping('url', 'sugarcrm.url')
            ->addConfigOption(
                'url',
                'u',
                InputOption::VALUE_REQUIRED,
                'Public url of SugarCRM.'
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
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $force = $input->getOption('force');
        $installer = new Installer(
            $this->getService('sugarcrm.application'),
            $this->getConfigOption($input, 'url'),
            $input->getOption('source'),
            $input->getOption('config')
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
