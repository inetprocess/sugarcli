<?php
/**
 * Check command to verify that Sugar is present and installed.
 */
namespace SugarCli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;

use SugarCli\Sugar\Util;
use SugarCli\Sugar\LangFileCleaner;

class CleanLangFilesCommand extends DefaultFromConfCommand
{
    protected function getDefaults()
    {
        return array('path' => 'sugarcrm.path');
    }

    protected function configure()
    {
        $this->setName("clean:langfiles")
            ->setDescription('Sort php arrays in language files to make it easier for vcs programs.')
            ->addOption(
                'no-sort',
                null,
                InputOption::VALUE_NONE,
                'Do not sort the files contents. It will still remove duplicates. Useful for testing.')
            ->addOption(
                'test',
                't',
                InputOption::VALUE_NONE,
                'Try to rewrite the files without modifying the contents. Imply --no-sort.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getHelper('logger');
        $path = $this->getDefaultOption($input, 'path');
        $sort = !$input->getOption('no-sort');
        $test = $input->getOption('test');
        if (!Util::isExtracted($path)) {
            $output->writeln('SugarCRM is not present in ' . $path . '.');
            return 11;
        }
        $cleaner = new LangFileCleaner($path, $logger);
        $cleaner->clean($sort, $test);
    }
}

