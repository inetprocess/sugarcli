<?php

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
    const BOOL_TRUE = "\xE2\x9C\x94";
    const BOOL_FALSE = "\xE2\x9C\x95";

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
                "Activate compact mode"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $module = $input->getOption('module');
        $compact = $input->getOption('compact');
        $sugarEP = $this->getService('sugarcrm.entrypoint');
        $validModules = array_keys($sugarEP->getBeansList());

        if (empty($module)) {
            throw new \InvalidArgumentException('You must define the module with --module');
        }

        $hooksList = array();

        $colsName = array('Weight', 'Description', 'File', 'Class', 'Method');
        if ($compact) {
            $colsName = array('Weight', 'Description', 'Method');
        }

        $headers = array(
            array(
                new TableCell(
                    PHP_EOL . "<comment>Hooks definition for $module</comment>" . PHP_EOL,
                    array('colspan' => count($colsName))
                )
            ),
            $colsName,
        );
        try {
            $table = new Table($output);
            // Output table
            //$table->setStyle('borderless');
            $table->setHeaders($headers);
            $tableData = array();
            $logicHook = new LogicHook($sugarEP);
            $hooksList = $logicHook->getModuleHooks($module);
            foreach ($hooksList as $hookType => $hooks) {
                $tableData[] = array(
                    new TableCell(
                        "<comment>--- Hook $hookType --- </comment>",
                        array('colspan' => count($colsName))
                    )
                );
                foreach ($hooks as $hook) {
                    // New line every 5 words
                    $words = explode(' ', $hook['Description']);
                    for ($i = 0; $i < count($words); $i++) {
                        if ($i !== 0 && $i%5 === 0) {
                            $words[$i] = PHP_EOL . $words[$i];
                        }
                    }
                    $hook['Description'] = implode(' ', $words);

                    // Remove useless fields if in compact mode
                    if ($compact) {
                        unset($hook['File']);
                        unset($hook['Class']);
                    }

                    $tableData[] = array_values($hook);
                }
                $tableData[] = new TableSeparator();
            }

            // Destroy last line
            unset($tableData[count($tableData) - 1]);

            $table->setRows($tableData);
            $table->render();
        } catch (BeanNotFoundException $e) {
            $msg = "Unknown module '$module'. Valid modules are:" . PHP_EOL;
            $msg.= '    - ' . implode(PHP_EOL . '    - ', $validModules);
            throw new \InvalidArgumentException($msg);
        }
    }
}
