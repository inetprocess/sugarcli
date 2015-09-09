<?php
namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

use Psr\Log\NullLogger;

use SugarCli\Console\Application;

class InventoryAgentCommandTest extends \PHPUnit_Framework_TestCase
{
    public function getFakeSugarPath()
    {
        return __DIR__ . '/metadata/fake_sugar';
    }

    public function getCommandTester()
    {
        $app = new Application();
        $app->configure();
        $app->getContainer()->set('logger', new NullLogger());
        $cmd = $app->find('inventory:agent');
        return new CommandTester($cmd);
    }

    public function testSuccess()
    {
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--account-name' => 'Test Agent',
            'server' => getenv('INVENTORY_URL'),
            'username' => getenv('INVENTORY_USERNAME'),
            'password' => getenv('INVENTORY_PASSWORD'),
        ));

        $this->assertEquals(0, $ret);
        $this->assertEquals('Successfuly sent report to inventory server.' . PHP_EOL, $cmd->getDisplay());
    }

    public function testFailInventory()
    {
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            '--path' => $this->getFakeSugarPath(),
            '--account-name' => 'Test Agent',
            'server' => 'test_server_invalid',
            'username' => 'invalid_user',
            'password' => 'invalid_pass',
        ));
        $this->assertEquals(4, $ret);
    }

    public function testFailSugar()
    {
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            '--path' => 'invalid sugar test',
            '--account-name' => 'Test Agent',
            'server' => getenv('INVENTORY_URL'),
            'username' => getenv('INVENTORY_USERNAME'),
            'password' => getenv('INVENTORY_PASSWORD'),
        ));

        $this->assertEquals(20, $ret);
    }
}
