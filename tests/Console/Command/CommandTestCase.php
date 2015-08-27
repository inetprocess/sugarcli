<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

use SugarCli\Console\Application;
use SugarCli\Tests\TestsUtil\TestLogger;

class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    protected $application;

    public function getApplication()
    {
        if (is_null($this->application)) {
            $this->application = new Application();
            $this->application->configure();
            $this->application->getContainer()->set('logger', new TestLogger());
        }
        return $this->application;
    }

    public function getCommandTester($command_name)
    {
        $cmd = $this->getApplication()->find($command_name);
        return new CommandTester($cmd);
    }
}
