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

namespace SugarCli\Console\Command\Database;

use Inet\SugarCRM\SugarQueryIterator;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check command to verify that Sugar is present and installed.
 */
class MassUpdateCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('database:massupdate')
            ->setDescription('Update all records in a module. Optionally set fields (Not implemented yet)')
            ->setHelp('Note: by default date_modified is not updated but seconds can be reset to `00`')
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addOption(
                'module',
                'm',
                InputOption::VALUE_REQUIRED,
                'Save records from this module'
            )
            ->addOption(
                'update-modified-by',
                'u',
                InputOption::VALUE_NONE,
                'By default fields `modified_user_id` and `date_modified` are not updated. '
                .'This option let SugarCRM update those fields'
            )
            ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getOption('module');
        if (empty($module)) {
            throw new \InvalidArgumentException('You must define the module with --module');
        }
        $entry_point = $this->getService('sugarcrm.entrypoint');
        $valid_modules = $entry_point->getBeansList();
        if (!array_key_exists($module, $valid_modules)) {
            $msg = "Unknown module '$module'. Valid modules are:" . PHP_EOL;
            $msg.= '    - ' . implode(PHP_EOL . '    - ', $valid_modules);
            throw new \InvalidArgumentException($msg);
        }

        /**
         * Disable ActivityStream for performance
         */
        $this->getService('sugarcrm.system')->disableActivity();

        /**
         * Build query
         */
        $query = new \SugarQuery();
        $query->from(\BeanFactory::newBean($module));

        /**
         * Get total count
         */
        $query->select()->setCountQuery();
        $result = $query->execute();
        $count = $result[0]['record_count'];
        $query->select = null;

        $progress = new ProgressBar($output, $count);
        $progress->start();
        $iter = new SugarQueryIterator($query, array('encode' => false, 'use_cache' => false));
        foreach ($iter as $id => $bean) {
            try {
                if (!$input->getOption('update-modified-by')) {
                    $bean->update_date_modified = false;
                    $bean->update_modified_by = false;
                }
                $bean->save();
            } catch (\Exception $e) {
                $output->writeln();
                if ($output instanceof ConsoleOutputInterface) {
                    $this->getApplication()->renderException($e, $output->getErrorOutput());
                } else {
                    $this->getApplication()->renderException($e, $output);
                }
            }
            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
    }
}
