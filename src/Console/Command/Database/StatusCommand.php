<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Vitaliy Volkivskiy
 * @author Emmanuel Dyan
 * @copyright 2005-2020 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command\Database;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends AbstractDatabaseCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('database:customfields:status')
            ->setDescription('Show the state of the <info>fields_meta_data</info> table compared to a *_cstm tables')
            ->setHelp(<<<EOH
Display fields which are not presented in <info>fields_meta_data</info> table with data from <module_name>_cstm tables.
EOH
            )->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Status for all tables'
            )->addOption(
                'table',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Status for only that table (repeat for multiple values)'
            );
    }

    /**
     * Main command entry point
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modules = $input->getOption('table');
        $modules = $this->unsetIncorrectModules($output, $modules);

        if ($input->getOption('force')) {
            $diff = $this->getFieldsDifference();
        } elseif (empty($modules)) {
            $output->writeln("<error>You should point at least one correct table name or 'force' option</error>");
            return;
        } else {
            $diff = $this->getFieldsDifference($modules);
        }

        foreach ($diff as $module => $fieldsNames) {
            if (!empty($modules) && empty($fieldsNames)) {
                $output->writeln("<info>No superflouous fields detected for pointed {$module}_cstm table</info>");
                continue;
            } elseif (empty($fieldsNames)) {
                continue;
            }

            $output->writeln("<comment>$module:</comment>");
            foreach ($fieldsNames as $fieldName) {
                $output->writeln("  $fieldName");
            }
        }
    }
}
