<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.6
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcli
 *
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Inet\SugarCRM\System as SugarSystem;

class SystemQuickRepairCommand extends AbstractConfigOptionCommand
{
    protected $messages = array();

    protected function configure()
    {
        $this->setName('system:quickrepair')
             ->setDescription('Do a quick repair and rebuild.')
             ->addConfigOptionMapping('path', 'sugarcrm.path')
             ->addOption(
                 'database',
                 'd',
                 InputOption::VALUE_NONE,
                 'Manage database changes.'
             )
             ->addOption(
                 'force',
                 'f',
                 InputOption::VALUE_NONE,
                 'Really execute the SQL queries (displayed by using -v).'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $sugarEP = $this->getService('sugarcrm.entrypoint');

        $output->writeln('<comment>Reparation</comment>: ');
        $progress = new ProgressIndicator($output);
        $progress->start('Starting...');
        $progress->advance();
        $sugarSystem = new SugarSystem($sugarEP);
        $progress->setMessage('Working...');
        $messages = $sugarSystem->repair($input->getOption('force'));
        $progress->finish('<info>Repair Done.</info>');

        if ($output->isVerbose()) {
            $output->writeln(PHP_EOL . '<comment>General Messages</comment>: ');
            $output->writeln($messages[0]);
        }

        if ($input->getOption('database') === false) {
            return;
        }

        $output->writeln(PHP_EOL . '<comment>Database Messages</comment>: ');
        // We have something to sync
        if (strpos($messages[1], 'Database tables are synced with vardefs') !== 0) {
            if ($input->getOption('force') === false) {
                $output->writeln($messages[1]);
                $output->writeln(PHP_EOL . '<error>You need to use --force to run the queries</error>');
            } else {
                $output->writeln('<info>Queries run, try another repair to verify</info>');
            }
        // Nothing to sync, default sugar message
        } else {
            $output->writeln($messages[1]);
        }
    }
}
