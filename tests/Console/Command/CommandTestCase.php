<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Psr\Log\NullLogger;

use Inet\SugarCRM\Application as SugarApp;
use Inet\SugarCRM\EntryPoint;

use SugarCli\Console\Application;
use SugarCli\Tests\TestsUtil\TestLogger;

class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    protected $application;

    public function getEntryPointInstance()
    {
        if (!EntryPoint::isCreated()) {
            $logger = new NullLogger;
            EntryPoint::createInstance(
                new SugarApp($logger, getenv('SUGARCLI_SUGAR_PATH')),
                '1'
            );
            $this->assertInstanceOf('Inet\SugarCRM\EntryPoint', EntryPoint::getInstance());
        }
        return EntryPoint::getInstance();
    }

    public function getApplication()
    {
        if (is_null($this->application)) {
            $this->application = new Application();
            $this->application->configure(
                new ArrayInput(array()),
                new StreamOutput(fopen('php://memory', 'w', false))
            );
            $this->application->getContainer()->set('logger', new TestLogger());
            $this->application->registerAllCommands();
        }
        return $this->application;
    }

    public function getCommandTester($command_name)
    {
        $cmd = $this->getApplication()->find($command_name);
        return new CommandTester($cmd);
    }
}
