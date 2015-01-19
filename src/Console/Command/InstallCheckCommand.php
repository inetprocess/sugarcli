<?php
namespace SugarCli\Console\Command;
/**
 * Check command to verify that Sugar is present and installed.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use SugarCli\Sugar\Sugar;

class InstallCheckCommand extends DefaultFromConfCommand
{
    protected function getDefaults()
    {
        return array('path' => 'sugarcrm.path');
    }

    protected function configure()
    {
        $this->setName("install:check")
            ->setDescription('Check if SugarCRM is installed and configured.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getDefaultOption($input, 'path');
        $sugar = new Sugar($path);
        if (!$sugar->isExtracted()) {
            $output->writeln('SugarCRM is not present in ' . $path . '.');
            return 11;
        }
        if (!$sugar->isInstalled()) {
            $output->writeln('SugarCRM is not installed in ' . $path . '.');
            return 12;
        }
        $output->writeln('SugarCRM is present and installed in ' . $path . '.');
    }
}

