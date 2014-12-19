<?php
namespace SugarCli\Install;
/**
 * Check command to verify that Sugar is present and installed.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use SugarCli\Sugar\Util;

class CheckCommand extends Command
{
    protected function configure()
    {
        $this->setName("install:check")
            ->setDescription('Check if SugarCRM is installed and configured.')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to SugarCRM installation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        if (!Util::isExtracted($path)) {
            $output->writeln('SugarCRM is not present in ' . $path . '.');
            return 11;
        }
        if (!Util::isInstalled($path)) {
            $output->writeln('SugarCRM is not installed in ' . $path . '.');
            return 12;
        }
        $output->writeln('SugarCRM is present and installed in ' . $path . '.');
    }
}

