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

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Inet\SugarCRM\LangFileCleaner;
use SugarCli\Console\ExitCode;

/**
 * Check command to verify that Sugar is present and installed.
 */
class CleanLangFilesCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('clean:langfiles')
            ->setDescription('Sort php arrays in language files to make it easier for vcs programs.')
            ->enableStandardOption('path')
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
        $path = $input->getOption('path');
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
