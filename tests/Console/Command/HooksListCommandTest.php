<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Psr\Log\NullLogger;

use Inet\SugarCRM\Application as SugarApp;
use Inet\SugarCRM\EntryPoint;
use SugarCli\Console\Application;

class HooksListCommandTest extends \PHPUnit_Framework_TestCase
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

    public function getCommandTester($cmd_name = 'hooks:list')
    {
        $app = new Application();
        $app->configure(
            new ArrayInput(array()),
            new StreamOutput(fopen('php://memory', 'w', false))
        );
        $app->setEntryPoint($this->getEntryPointInstance());
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
     * @expectedExceptionMessageRegExp #.*Unknown module 'TOTO'.*#
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
        $this->assertContains('Hooks definition for Opportunities', $output, $msg);
        $this->assertContains('before_save', $output, $msg);
    }

    public function testListHookCompactRightModule()
    {
        $cmd = $this->getCommandTester();
        $result = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Meetings',
            '--compact' => null,
        ));
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $msg = 'Check that your Sugar instance has the default Hook before_relationship_update for Meetings';
        $this->assertContains('Hooks definition for Meetings', $output, $msg);
        $this->assertContains('before_relationship_update', $output, $msg);
        $this->assertNotContains('| Class', $output, $msg);
    }

    public function testListHookRightModuleEmptyHooks()
    {
        $cmd = $this->getCommandTester();
        $result = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Leads'
        ));
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $msg = 'Check that your Sugar instance has no Hooks for Leads';
        $this->assertContains('Hooks definition for Leads', $output, $msg);
        $this->assertContains('No Hooks for that module', $output, $msg);
    }
}
