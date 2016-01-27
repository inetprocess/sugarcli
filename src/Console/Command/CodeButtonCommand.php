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
use Inet\SugarCRM\Bean as BeanManager;
use Inet\SugarCRM\MetadataParser;

class CodeButtonCommand extends AbstractConfigOptionCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('code:button')
            ->setDescription('Add or delete a button in a module')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
            ->addOption(
                'module',
                'm',
                InputOption::VALUE_REQUIRED,
                'Module name.'
            )->addOption(
                'action',
                'a',
                InputOption::VALUE_REQUIRED,
                'Action: "add" / "delete"',
                'add'
            )->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'Button Name'
            )->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'For now only "dropdown"',
                'dropdown'
            )->addOption(
                'javascript',
                'j',
                InputOption::VALUE_NONE,
                '[EXPERIMENTAL] Also create the JS'
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
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $ep = $this->getService('sugarcrm.entrypoint');
        $bm = new BeanManager($ep);
        $module = $input->getOption('module');
        // Get the file as a parameter
        if (empty($module)) {
            $moduleList = array_keys($ep->getBeansList());
            $msg = 'You must define the module with --module';
            $msg.= PHP_EOL . PHP_EOL . 'List of Available modules: ' . PHP_EOL;
            $msg.= '    - ' . implode(PHP_EOL . '    - ', $moduleList);
            throw new \InvalidArgumentException($msg);
        }

        $action = $input->getOption('action');
        if (empty($action) || !in_array($action, array('add', 'delete'))) {
            throw new \InvalidArgumentException('Action must be "--action add" or "--action delete"');
        }

        $name = $input->getOption('name');
        if (empty($name)) {
            throw new \InvalidArgumentException('You must define the button\'s name');
        }

        $type = $input->getOption('type');
        if (empty($type) || !in_array($type, array('dropdown'))) {
            throw new \InvalidArgumentException('Type must be "--type dropdown" (for now)');
        }

        $utils = new MetadataParser($ep);
        $langPath = "custom/Extension/modules/$module/Ext/Language";
        $recordView = "custom/modules/$module/clients/base/views/record/record.php";
        switch ($action) {
            case 'add':
                $utils->addButtonInRecordView($module, $input->getOption('name'));
                if ($input->getOption('javascript') === true) {
                    $output->writeln('<comment>--javascript is experimental !</comment>');
                    $this->addJsToRecord($module, $name, $input, $output);
                }
                $output->writeln('Button Added as well as its label.');
                $output->writeln("Check <info>$langPath</info> and <info>$recordView</info>");
                break;
            case 'delete':
                $utils->deleteButtonInRecordView($module, $input->getOption('name'));
                $output->writeln('Button Deleted as well as its label.');
                $output->writeln("Check <info>$langPath</info> and <info>$recordView</info>");
                $output->writeln("You must manually remove your method $name in the record.js file");
                break;
        }
    }

    /**
     * Add Javascript to the record view
     *
     * @param string          $module
     * @param string          $name
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function addJsToRecord($module, $name, InputInterface $input, OutputInterface $output)
    {
        // identify first the record.js file
        $recordJs = $this->getConfigOption($input, 'path');
        $recordJs.= "/custom/modules/$module/clients/base/views/record/record.js";
        $btnName = 'btn' . ucfirst(strtolower($name));
        $today = date('Y-m-d \a\t H:i:s');
        if (file_exists($recordJs)) {
            $jsCode = "    ,
    // Added by sugarcli ($today)
    $btnName: function() {
        console.log('Hi');
    }";
            $jsContent = file_get_contents($recordJs);
            $pattern = "|^(.+)initialize: function(.+)},(.+)}\)$|s";
            preg_match($pattern, $jsContent, $matches);
            if (count($matches) !== 4) {
                $output->writeln("Can't update the JS. Try adding manually:" . PHP_EOL . $jsCode . PHP_EOL);

                return false;
            }
            $jsContent = $matches[1] . 'initialize: function' . $matches[2];
            $jsContent.= "    this.context.on('button:$btnName:click', this.$btnName, this);";
            $jsContent.= PHP_EOL . '    },' . $matches[3];
            $jsContent.= $jsCode . PHP_EOL;
            $jsContent.= '})' . PHP_EOL;
            file_put_contents($recordJs, $jsContent);
            $output->writeln('JS file exist, updated it');
        } else {
            $jsContent = file_get_contents(__DIR__ . '/../../../res/code_templates/record.js');
            // replace the vars
            $jsContent = str_replace('[[name]]', $btnName, $jsContent);
            file_put_contents($recordJs, $jsContent);
            $output->writeln("JS created check $recordJs");
        }

        $this->doQuickRepair($output);
    }
}
