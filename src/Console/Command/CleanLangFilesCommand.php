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

use Inet\SugarCRM\Application;
use Inet\SugarCRM\LangFileCleaner;

use SugarCli\Console\ExitCode;

class CleanLangFilesCommand extends AbstractDefaultFromConfCommand
{
    protected function getConfigOptionMapping()
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
                'Do not sort the files contents. It will still remove duplicates. Useful for testing.'
            )
            ->addOption(
                'test',
                't',
                InputOption::VALUE_NONE,
                'Try to rewrite the files without modifying the contents. Imply --no-sort.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getDefaultOption($input, 'path');
        $this->setSugarPath($path);
        $sugar = $this->getService('sugarcrm.application');
        $sort = !$input->getOption('no-sort');
        $test = $input->getOption('test');
        if (!$sugar->isValid()) {
            $output->writeln('SugarCRM is not present in ' . $path . '.');
            return ExitCode::EXIT_NOT_EXTRACTED;
        }
        $cleaner = new LangFileCleaner($sugar);
        $cleaner->clean($sort, $test);
    }
}
