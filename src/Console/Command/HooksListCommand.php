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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Inet\SugarCRM\Exception\BeanNotFoundException;
use Inet\SugarCRM\LogicHook;

class HooksListCommand extends AbstractConfigOptionCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('hooks:list')
            ->setDescription('List hooks of the SugarCRM instance.')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
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
     * @param     InputInterface     $input
     * @param     OutputInterface    $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $module = $input->getOption('module');
        $compact = $input->getOption('compact');
        $sugarEP = $this->getService('sugarcrm.entrypoint');
        $validModules = array_keys($sugarEP->getBeansList());

        if (empty($module)) {
            throw new \InvalidArgumentException('You must define the module with --module');
        }

        $colsName = array('Weight', 'Description', 'File', 'Class', 'Method', 'Defined In');
        if ($compact) {
            $colsName = array('Weight', 'Description', 'Method');
        }

        $title = new TableCell("<comment>Hooks definition for $module</comment>", array('colspan' => count($colsName)));
        $headers = array(array($title), $colsName);

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($this->generateTableRows($logicHook));
        $table->render();
    }

    /**
     * Generate all the rows by getting the hooks from sugarcrm
     * @return    [type]    [description]
     */
    protected function generateTableRows()
    {
        $tableData = array();
        try {
            $logicHook = new LogicHook($sugarEP);
            $hooksList = $logicHook->getModuleHooks($module);
        } catch (BeanNotFoundException $e) {
            $msg = "Unknown module '$module'. Valid modules are:" . PHP_EOL;
            $msg.= '    - ' . implode(PHP_EOL . '    - ', $validModules);
            throw new \InvalidArgumentException($msg);
        }

        if (empty($hooksList)) {
            $tableData[] = array(
                new TableCell('<error>No Hooks for that module</error>', array('colspan' => count($colsName)))
            );
        }

        $hooksComs = $logicHook->getModulesLogicHooksDef();
        $procHooks = 0;
        $nbHooks = count($hooksList);
        foreach ($hooksList as $type => $hooks) {
            $com = 'No description';
            if (array_key_exists($type, $hooksComs)) {
                $com = $hooksComs[$type];
            }
            $tableData[] = array(
                new TableCell("<comment>$type ($com)</comment>", array('colspan' => count($colsName)))
            );

            foreach ($hooks as $hook) {
                // New line every 5 words
                $words = explode(' ', $hook['Description']);
                for ($i = 0; $i < count($words); $i++) {
                    $words[$i] = ($i !== 0 && $i%5 === 0 ? PHP_EOL : '') . $words[$i];
                }
                $hook['Description'] = implode(' ', $words);

                // Remove useless fields if in compact mode
                if ($compact) {
                    unset($hook['File']);
                    unset($hook['Class']);
                    unset($hook['Defined In']);
                }

                $tableData[] = array_values($hook);
            }

            // Create a separator if I am not
            $procHooks++;
            if ($procHooks < $nbHooks) {
                $tableData[] = new TableSeparator();
            }
        }

        return $tableData;
    }
}
