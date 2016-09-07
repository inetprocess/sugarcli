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

namespace SugarCli\Console\Command\Code;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Inet\SugarCRM\MetadataParser;
use SugarCli\Console\Command\AbstractConfigOptionCommand;

class ButtonCommand extends AbstractConfigOptionCommand
{
    /**
     * Store Options values
     *
     * @var array
     */
    protected $options = array();

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('code:button')
            ->setDescription('Add or delete a button in a module')
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
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
        $this->checkOptions($input);

        $utils = new MetadataParser($this->getService('sugarcrm.entrypoint'));
        $langPath = "custom/Extension/modules/{$this->options['module']}/Ext/Language";
        $recordView = "custom/modules/{$this->options['module']}/clients/base/views/record/record.php";
        switch ($this->options['action']) {
            case 'add':
                $utils->addButtonInRecordView($this->options['module'], $this->options['name']);
                if ($input->getOption('javascript') === true) {
                    $output->writeln('<comment>--javascript is experimental !</comment>');
                    $this->addJsToRecord($input, $output);
                }
                $output->writeln('Button Added as well as its label.');
                $output->writeln("Check <info>$langPath</info> and <info>$recordView</info>");
                break;
            case 'delete':
                $utils->deleteButtonInRecordView($this->options['module'], $this->options['name']);
                $output->writeln('Button Deleted as well as its label.');
                $output->writeln("Check <info>$langPath</info> and <info>$recordView</info>");
                $output->writeln("You must manually remove your method {$this->options['name']} in the record.js file");
                break;
        }
    }

    /**
     * Check required options and their values
     *
     * @param InputInterface $input
     */
    protected function checkOptions(InputInterface $input)
    {
        $this->options['module'] = $input->getOption('module');
        // Get the file as a parameter
        if (empty($this->options['module'])) {
            $moduleList = array_keys($this->getService('sugarcrm.entrypoint')->getBeansList());
            $msg = 'You must define the module with --module';
            $msg.= PHP_EOL . PHP_EOL . 'List of Available modules: ' . PHP_EOL;
            $msg.= '    - ' . implode(PHP_EOL . '    - ', $moduleList);
            throw new \InvalidArgumentException($msg);
        }

        $this->options['action'] = $input->getOption('action');
        if (empty($this->options['action']) || !in_array($this->options['action'], array('add', 'delete'))) {
            throw new \InvalidArgumentException('Action must be "--action add" or "--action delete"');
        }

        $this->options['name'] = $input->getOption('name');
        if (empty($this->options['name'])) {
            throw new \InvalidArgumentException('You must define the button\'s name');
        }

        $this->options['type'] = $input->getOption('type');
        if (empty($this->options['type']) || !in_array($this->options['type'], array('dropdown'))) {
            throw new \InvalidArgumentException('Type must be "--type dropdown" (for now)');
        }
    }

    /**
     * Add Javascript to the record view
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function addJsToRecord(InputInterface $input, OutputInterface $output)
    {
        // identify first the record.js file
        $recordJs = $input->getOption('path');
        $recordJs.= "/custom/modules/{$this->options['module']}/clients/base/views/record/record.js";
        $btnName = 'btn' . ucfirst(strtolower($this->options['name']));
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
            $jsContent = file_get_contents(__DIR__ . '/../../../../res/code_templates/record.js');
            // replace the vars
            $jsContent = str_replace('[[name]]', $btnName, $jsContent);
            file_put_contents($recordJs, $jsContent);
            $output->writeln("JS created check $recordJs");
        }

        $this->getService('sugarcrm.system')->repairAll();
        $output->writeln('<info>Repair Done.</info>');
    }
}
