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

use CSanquer\ColibriCsv\CsvWriter;
use CSanquer\ColibriCsv\Dialect;
use Inet\SugarCRM\Exception\BeanNotFoundException;
use Inet\SugarCRM\LogicHook;
use SugarCli\Utils\Utils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

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
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Specify the output format <comment>(text|csv)</comment>',
                'text'
            )
            ->addOption(
                'compact',
                'c',
                InputOption::VALUE_NONE,
                'Activate compact mode output'
            )
            ->addOption(
                'csv-option',
                'C',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify option for csv export. '
                . "Ex: -C 'delimiter=,'"
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

        switch($input->getOption('format')) {
            case 'text':
                $this->printTextTable($input, $output);
                break;
            case 'csv':
                $this->printCsv($input, $output);
                break;
            default:
                throw new \InvalidArgumentException('Format argument not recognized. Available values are <text|csv>');
        }
    }

    protected function fetchHookData($module)
    {
        $entryPoint = $this->getService('sugarcrm.entrypoint');
        $validModules = array_keys($entryPoint->getBeansList());
        try {
            $logicHook = new LogicHook($entryPoint);
            $hooksData = $logicHook->getModuleHooks($module);
        } catch (BeanNotFoundException $e) {
            $msg = "Unknown module '$module'. Valid modules are:" . PHP_EOL;
            $msg.= '    - ' . implode(PHP_EOL . '    - ', $validModules);
            throw new \InvalidArgumentException($msg);
        }
        // Try to fetch file if a namespaced class is specified
        if (class_exists('ReflectionClass')) {
            $fs = new Filesystem();
            foreach ($hooksData as $hookType => $hooksList) {
                foreach ($hooksList as $hookIdx => $hook) {
                    if (empty($hook['File'])) {
                        $reflex = new \ReflectionClass($hook['Class']);
                        $hooksData[$hookType][$hookIdx]['File'] = rtrim($fs->makePathRelative(
                            $reflex->getFileName(),
                            $entryPoint->getPath()
                        ), '/');
                    }
                }
            }
        }
        return $hooksData;
    }

    protected function printCsv(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getOption('module');
        $options = array(
            'encoding' => 'UTF-8',
            'first_row_header' => true,
            'delimiter' => ',',
        );
        foreach ($input->getOption('csv-option') as $option) {
            list($key, $value) = explode('=', $option);
            $options[$key] = $value;
        }
        $writer = new CsvWriter($options);
        $writer->createTempStream();
        $hooksDef = $this->fetchHookData($module);
        foreach ($hooksDef as $hookType => $hooksList) {
            foreach ($hooksList as $hook) {
                $writer->writeRow(array_merge(
                    array('Type' => $hookType),
                    $hook
                ));
            }
        }
        $output->write($writer->getFileContent());
        $writer->close();
    }

    protected function printTextTable(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getOption('module');
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
        $hooksList = $this->fetchHookData($module);

        $tableData = array();
        if (empty($hooksList)) {
            $tableData[] = array(
                new TableCell('<error>No Hooks for that module</error>', array('colspan' => $numCols))
            );
        }

        $logicHook = new LogicHook($this->getService('sugarcrm.entrypoint'));
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
