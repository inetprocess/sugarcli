<?php
namespace SugarCli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Application;
use SugarCli\Console\ExitCode;

/**
 * Check command to verify that Sugar is present and installed.
 */
class InstallCheckCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('install:check')
            ->setDescription('Check if SugarCRM is installed and configured.')
            ->addConfigOptionMapping('path', 'sugarcrm.path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getConfigOption($input, 'path');
        $this->setSugarPath($path);
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
