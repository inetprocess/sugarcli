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

namespace SugarCli\Console\Command\Plugins;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use SugarCli\SugarCRM\Database\Plugins;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Utils\Utils;

abstract class AbstractPluginsCommand extends AbstractConfigOptionCommand
{
    const PLUGINS_PATH = '../db/plugins.yaml';

    public function buildPluginsPath($sugarcrm_path, $plugins_path)
    {
        if ($plugins_path === $this->getPluginsFileDefault()) {
            if ($sugarcrm_path !== null) {
                $plugins_path = Utils::makeConfigPathRelative($sugarcrm_path, self::PLUGINS_PATH);
            }
        }
        return $plugins_path;
    }

    public function getPluginsFileDefault()
    {
        return '<SUGAR_PATH>/' . self::PLUGINS_PATH;
    }

    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        $plugins_option = $this->getDefinition()->getOption('plugins-file');
        $plugins_path = $this->buildPluginsPath(
            $this->getDefinition()->getOption('path')->getDefault(),
            $plugins_option->getDefault()
        );
        $plugins_option->setDefault($plugins_path);
    }

    protected function configure()
    {
        $this->enableStandardOption('path')
        ->addConfigOption(
            'plugins.file',
            'plugins-file',
            'm',
            InputOption::VALUE_REQUIRED,
            'Path to the plugins file',
            $this->getPluginsFileDefault(),
            false,
            function ($option_name, InputInterface $input, Command $command) {
                $plugins_path = $input->getOption($option_name);
                if ($plugins_path === $command->getPluginsFileDefault()) {
                    $plugins_path = $command->buildPluginsPath($input->getOption('path'), $plugins_path);
                    $command->getDefinition()->getOption($option_name)->setDefault($plugins_path);
                }
            }
        );
    }

    protected function setDiffOptions(array $descriptions)
    {
        $this->addOption(
            'add',
            'a',
            InputOption::VALUE_NONE,
            $descriptions['add']
        )
        ->addOption(
            'del',
            'd',
            InputOption::VALUE_NONE,
            $descriptions['del']
        )
        ->addOption(
            'update',
            'u',
            InputOption::VALUE_NONE,
            $descriptions['update']
        )
        ->addArgument(
            'plugins',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'Filter the command to only apply to this list of plugins'
        );
    }

    protected function getDiffOptions(InputInterface $input)
    {
        $res = Plugins::DIFF_NONE;
        $options_map = array(
            'add' => Plugins::DIFF_ADD,
            'del' => Plugins::DIFF_DEL,
            'update' => Plugins::DIFF_UPDATE
        );
        foreach ($options_map as $opt => $diff_opt) {
            if ($input->getOption($opt)) {
                $res |= $diff_opt;
            }
        }

        return array(
            'mode' => ($res) ?: Plugins::DIFF_ALL,
            'plugins' => str_replace('.', '', $input->getArgument('plugins'))
        );
    }

    public function getProgramName()
    {
        return $_SERVER['argv'][0];
    }
}
