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
    const CONFIG_NAME = 'sugarclirc';
    public $config_paths = array();

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->setConfigPaths(array(
            '/etc/' . self::CONFIG_NAME,
            getenv('HOME') . '/.' . self::CONFIG_NAME,
            '.' . self::CONFIG_NAME
        ));
    }

    /**
     * Init commands
     */
    public function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new \SugarCli\Console\Command\InstallCheckCommand();
        $commands[] = new \SugarCli\Console\Command\InstallRunCommand();
        $commands[] = new \SugarCli\Console\Command\InstallGetConfigCommand();
        $commands[] = new \SugarCli\Console\Command\CleanLangFilesCommand();
        return $commands;
    }

    public function setConfigPaths(array $config_paths)
    {
        $this->config_paths = $config_paths;
    }

    public function run()
    {
        $config = new Config($this->config_paths);
        $config->load();
        $output = new ConsoleOutput();
        $this->getHelperSet()->set(new LoggerHelper($output));
        $this->getHelperSet()->set($config);

        return parent::run(null, $output);
    }
}

