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
class UserListCommandTest extends \PHPUnit_Framework_TestCase
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

    public function getCommandTester($cmd_name = 'user:list')
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

    public function testList()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
    }

    public function testListOne()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--username' => 'admin',
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
    }

    public function testListOneNotFound()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--username' => 'Invalid user',
        ));
        $this->assertEquals(22, $cmd->getStatusCode());
    }

    public function testListJson()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--format' => 'json',
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
    }

    public function testListInvalidFormat()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--format' => 'invalid format',
        ));
        $this->assertEquals(3, $cmd->getStatusCode());
    }

    public function testRawOutput()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--format' => 'json',
            '--raw' => null,
            '--username' => 'admin',
            '-F' => 'id,user_name',
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $data = json_decode($cmd->getDisplay(), true);
        $expected_data = array(array(
            'id' => '1',
            'user_name' => 'admin',
        ));
        $this->assertEquals($expected_data, $data);
    }

    public function testPrettyOutput()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--format' => 'json',
            '--lang' => 'fr_FR',
            '--username' => 'admin',
            '-F' => 'id,user_name',
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $data = json_decode($cmd->getDisplay(), true);
        $expected_data = array(array(
            'ID' => '1',
            'Login' => 'admin',
        ));
        $this->assertEquals($expected_data, $data);
    }
}
