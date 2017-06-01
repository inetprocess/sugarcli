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
use Symfony\Component\Filesystem\Filesystem;
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
            ->setDescription('List hooks of the SugarCRM instance')
            ->setHelp(<<<EOHELP
List the hooks defined for the module. For each hook display the following information:

* <comment>Weight</comment>\tOrder of execution
* <comment>Description</comment>\tShort description
* <comment>File</comment>\tFile containing the source code for the hook
* <comment>Class</comment>\tPHP Class name
* <comment>Method</comment>\tMethod called when the hook is triggered
* <comment>Defined In</comment>\tFile where the hook is configured
EOHELP
            )
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addOption(
                'module',
                'm',
                InputOption::VALUE_REQUIRED,
                "List hooks from this module"
            )
            ->addOption(
                'compact',
                'c',
                InputOption::VALUE_NONE,
                'Activate compact mode output'
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

        $output->writeln("<comment>Hooks definition for $module</comment>");

        $table = new Table($output);
        $headers = $this->generateTableHeaders($module, $input->getOption('compact'));
        $table->setHeaders($headers);
        $table->setRows($this->generateTableRows($module, $input->getOption('compact'), count($headers)));
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
        $colsName = array('Weight', 'Description', 'File', 'Class::Method', 'Defined In');
        if ($compact) {
            $colsName = array('Weight', 'Description', 'Method');
        }
        return $colsName;
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
        $entryPoint = $this->getService('sugarcrm.entrypoint');
        $validModules = array_keys($entryPoint->getBeansList());
        try {
            $logicHook = new LogicHook($entryPoint);
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
                } else {
                    if (class_exists('ReflectionClass') && empty($hook['File'])) {
                        $reflex = new \ReflectionClass($hook['Class']);
                        $fs = new Filesystem();
                        $hook['File'] = rtrim($fs->makePathRelative(
                            $reflex->getFileName(),
                            $entryPoint->getPath()
                        ), '/');
                    }
                    $hook = array_values($hook);
                    array_splice($hook, 3, 2, $hook[3] . '::' . $hook[4]);
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
