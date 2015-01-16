<?php

namespace SugarCli\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Output\ConsoleOutput;

use SugarCli\Util\LoggerHelper;

/**
 * Run console application.
 * Configuration files can be found in:
 *   /etc/sugarclirc
 *   $HOME/.sugarclirc
 */
class Application extends BaseApplication
{
    public $config_path;

    /**
     * Init commands
     */
    public function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new \SugarCli\Install\CheckCommand();
        $commands[] = new \SugarCli\Install\RunCommand();
        $commands[] = new \SugarCli\Install\GetConfigCommand();
        $commands[] = new \SugarCli\Clean\LangFilesCommand();
        return $commands;
    }

    public function run()
    {
        $output = new ConsoleOutput();
        $this->getHelperSet()->set(new LoggerHelper($output));

        return parent::run(null, $output);
    }
}

