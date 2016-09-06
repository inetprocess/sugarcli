<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Psr\Log\NullLogger;

use Inet\SugarCRM\Application as SugarApp;
use Inet\SugarCRM\EntryPoint;
use SugarCli\Console\Application;

/**
 * @group sugarcrm-path
 */
class ExtractFieldsCommandTest extends \PHPUnit_Framework_TestCase
{
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

    public function getCommandTester($cmd_name = 'extract:fields')
    {
        $app = new Application();
        $app->configure(
            new ArrayInput(array()),
            new StreamOutput(fopen('php://memory', 'w', false))
        );
        $app->setEntryPoint($this->getEntryPointInstance());
        $app->registerAllCommands();
        $cmd = $app->find($cmd_name);
        return new CommandTester($cmd);
    }

    /** Missing Param module: exception thrown
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp #You must define the module with --module#
     */
    public function testListMissingParam()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
    }

    /** Define a wrong module: exception thrown
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp #TOTO does not exist in SugarCRM, I cannot retrieve anything#
     */
    public function testListWrongParam()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'TOTO',
        ));
    }

    public function testListHookRightModule()
    {
        $cmd = $this->getCommandTester();
        $result = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Opportunities'
        ));
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $msg = 'Check that your Sugar instance has the default Hook before_relationship_update for Opportunities';
        $this->assertContains('All fields for Opportunities written in', $output, $msg);
        $this->assertContains('All relationships for Opportunities written in', $output, $msg);
    }
}
