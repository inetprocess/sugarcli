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
use SugarCli\Console\ExitCode;
use SugarCli\Console\Command\AbstractConfigOptionCommand;

/**
 * Check command to verify that Sugar is present and installed.
 */
class CheckCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('install:check')
            ->setDescription('Check if SugarCRM is installed and configured.')
            ->enableStandardOption('path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('path');
        $sugar = $this->getService('sugarcrm.application');
        if (!$sugar->isValid()) {
            $output->writeln('SugarCRM is not present in ' . $path . '.');

            return ExitCode::EXIT_NOT_EXTRACTED;
        }
        if (!$sugar->isInstalled()) {
            $output->writeln('SugarCRM is not installed in ' . $path . '.');

            return ExitCode::EXIT_NOT_INSTALLED;
        }
        $output->writeln('SugarCRM is present and installed in ' . $path . '.');
    }
}
