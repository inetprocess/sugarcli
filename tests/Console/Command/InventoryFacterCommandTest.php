<?php
namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

use SugarCli\Console\Command\InventoryFacterCommand;

class InventoryFacterCommandTest extends \PHPUnit_Framework_TestCase
{
    public function getCommandTester()
    {
        $app = new Application();
        $app->add(new InventoryFacterCommand());

        $cmd = $app->find('inventory:facter');
        return new CommandTester($cmd);
    }

    public function testDefault()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array());

        $output = $cmd->getDisplay();
        $json = json_decode($output, true);
        $this->assertArrayHasKey('system', $json);
        $this->assertNotEmpty($json['system']);
        $this->assertArrayHasKey('sugarcrm', $json);
    }

    public function testInvalidFormat()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(
            array('--format' => 'abc')
        );
        $this->assertEquals(3, $cmd->getStatusCode());
    }

    public function testSugarcrmOnly()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array('--format' => 'json', 'source' => array('sugarcrm')));

        $output = $cmd->getDisplay();
        $json = json_decode($output, true);
        $this->assertArrayHasKey('system', $json);
        $this->assertArrayHasKey('sugarcrm', $json);
        $this->assertEmpty($json['system']);
    }

    public function testXmlFormat()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array('--format' => 'xml'));

        $output = $cmd->getDisplay();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $output);
    }

    public function testYmlFormat()
    {
        $cmd = $this->getCommandTester();
        $cmd->execute(array('--format' => 'yml'));

        $output = $cmd->getDisplay();
        $yml = Yaml::parse($output);
        $this->assertArrayHasKey('system', $yml);
        $this->assertArrayHasKey('sugarcrm', $yml);
        $this->assertNotEmpty($yml['system']);
    }
}
