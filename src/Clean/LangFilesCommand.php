<?php
namespace SugarCli\Clean;
/**
 * Check command to verify that Sugar is present and installed.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Logger\ConsoleLogger;

use SugarCli\Sugar\Util;
use SugarCli\Sugar\LangFileCleaner;

class LangFilesCommand extends Command 
{
    protected function configure()
    {
        $this->setName("clean:langfiles")
            ->setDescription('Sort php arrays in language files to make it easier for vcs programs.')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to sugarcrm instance');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $path = $input->getArgument('path');
        if(!Util::is_extracted($path)) {
            $output->writeln('SugarCRM is not present in ' . $path . '.');
            exit(11);
        }
        if(!Util::is_installed($path)) {
            $output->writeln('SugarCRM is not installed in ' . $path . '.');
            exit(12);
        }
        $cleaner = new LangFileCleaner($path, $logger);
        $cleaner->clean();
    }
}
