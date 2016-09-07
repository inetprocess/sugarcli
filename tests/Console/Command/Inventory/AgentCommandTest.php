<?php
namespace SugarCli\Tests\Console\Command\Inventory;

use Symfony\Component\Yaml\Yaml;
use SugarCli\Tests\Console\Command\CommandTestCase;

class AgentCommandTest extends CommandTestCase
{
    public static $cmd_name = 'inventory:agent';

    public function getFakeSugarPath()
    {
        return __DIR__ . '/metadata/fake_sugar';
    }

    /**
     * @group inventory
     * @group sugarcrm-path
     */
    public function testSuccess()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--account-name' => 'Test Agent',
            '--custom-fact' => array('sugarcrm.context:dev'),
            'server' => getenv('INVENTORY_URL'),
            'username' => getenv('INVENTORY_USERNAME'),
            'password' => getenv('INVENTORY_PASSWORD'),
        ));

        $this->assertEquals(0, $ret);
        $this->assertEquals('Successfuly sent report to inventory server.' . PHP_EOL, $cmd->getDisplay());
    }

    /**
     * @group sugarcrm-path
     */
    public function testFailInventory()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--account-name' => 'Test Agent',
            'server' => 'test_server_invalid',
            'username' => 'invalid_user',
            'password' => 'invalid_pass',
        ));
        $this->assertEquals(4, $ret);
    }

    public function testFailSugar()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
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
