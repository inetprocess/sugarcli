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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Inet\SugarCRM\Exception\BeanNotFoundException;
use Inet\SugarCRM\LogicHook;
use SugarCli\Utils\Utils;

class HooksListCommand extends AbstractConfigOptionCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('hooks:list')
            ->setDescription('List hooks of the SugarCRM instance.')
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addOption(
                'module',
                'm',
                InputOption::VALUE_REQUIRED,
                "Module's name."
            )
            ->addOption(
                'compact',
                null,
                InputOption::VALUE_NONE,
                'Activate compact mode'
            );
    }

    /**
     * Run the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getOption('module');

        if (empty($module)) {
            throw new \InvalidArgumentException('You must define the module with --module');
        }

        $table = new Table($output);
        $headers = $this->generateTableHeaders($module, $input->getOption('compact'));
        $table->setHeaders($headers);
        $table->setRows($this->generateTableRows($module, $input->getOption('compact'), count($headers[1])));
        $table->render();
    }

    /**
     * Generate the headers
     *
     * @param bool $compact
     *
     * @return array
     */
    protected function generateTableHeaders($module, $compact)
    {
        $colsName = array('Weight', 'Description', 'File', 'Class', 'Method', 'Defined In');
        if ($compact) {
            $colsName = array('Weight', 'Description', 'Method');
        }

        $title = new TableCell("<comment>Hooks definition for $module</comment>", array('colspan' => count($colsName)));
        $headers = array(array($title), $colsName);

        return $headers;
    }

    /**
     * Generate all the rows by getting the hooks from sugarcrm
     *
     * @param string  $module
     * @param bool    $compact
     * @param integer $numCols
     *
     * @return array
     */
    protected function generateTableRows($module, $compact, $numCols)
    {
        $validModules = array_keys($this->getService('sugarcrm.entrypoint')->getBeansList());
        try {
            $logicHook = new LogicHook($this->getService('sugarcrm.entrypoint'));
            $hooksList = $logicHook->getModuleHooks($module);
        } catch (BeanNotFoundException $e) {
            $msg = "Unknown module '$module'. Valid modules are:" . PHP_EOL;
            $msg.= '    - ' . implode(PHP_EOL . '    - ', $validModules);
            throw new \InvalidArgumentException($msg);
        }

        $tableData = array();
        if (empty($hooksList)) {
            $tableData[] = array(
                new TableCell('<error>No Hooks for that module</error>', array('colspan' => $numCols))
            );
        }

        $hooksComs = $logicHook->getModulesLogicHooksDef();
        $procHooks = 0;
        $nbHooks = count($hooksList);
        foreach ($hooksList as $type => $hooks) {
            $com = (array_key_exists($type, $hooksComs) ? $hooksComs[$type] : 'No description');
            $tableData[] = array(
                new TableCell("<comment>$type ($com)</comment>", array('colspan' => $numCols))
            );

            foreach ($hooks as $hook) {
                $hook['Description'] = Utils::newLineEveryXWords($hook['Description'], 5);

                // Remove useless fields if in compact mode
                if ($compact) {
                    unset($hook['File']);
                    unset($hook['Class']);
                    unset($hook['Defined In']);
                }

                $tableData[] = array_values($hook);
            }

            // Create a separator if I am not
            if (++$procHooks < $nbHooks) {
                $tableData[] = new TableSeparator();
            }
        }

        return $tableData;
    }
}
