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

use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use SugarCli\Utils\CurlGithubStrategy;

class SelfUpdateCommand extends Command
{
    protected function configure()
    {
        $this->setName('self-update')
            ->setAliases(array('selfupdate'))
            ->setDescription('Update the <info>sugarcli.phar</info> with the latest stable version.')
            ->addOption(
                'rollback',
                'r',
                InputOption::VALUE_NONE,
                'Rollback to the previous version of <info>sugracli.phar</info>'
            )
            ;
    }

    /**
     * Run the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater(null, false);
        $updater->setStrategyObject(new CurlGithubStrategy());
        $updater->getStrategy()->setPackageName('inetprocess/sugarcli');
        $updater->getStrategy()->setPharName('sugarcli.phar');
        $updater->getStrategy()->setCurrentLocalVersion($this->getApplication()->getVersion());
        if ($input->getOption('rollback')) {
            $updater->rollback();
        } else {
            $result = $updater->update();
            if ($result) {
                $output->writeln('Successfuly updated');
            } else {
                $output->writeln('Already up to date');
            }
        }
    }
}
