<?php

namespace SugarCli\Tests\Inventory;

use SugarCli\Inventory\CommandFactsProvider;

class CommandFactsProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testFlatResults()
    {
        $cmd_provider = new CommandFactsProvider();
        $reflex = new \ReflectionClass($cmd_provider);
        $cmd = $reflex->getProperty('cmd');
        $cmd->setAccessible(true);
        $cmd->setValue($cmd_provider, 'echo "foo=bar"');
        $this->assertEquals(
            array('foo' => 'bar'),
            $cmd_provider->getFacts()
        );
    }

    public function testJsonResults()
    {
        $cmd_provider = new CommandFactsProvider();
        $reflex = new \ReflectionClass($cmd_provider);
        $cmd = $reflex->getProperty('cmd');
        $cmd->setAccessible(true);
        $cmd->setValue($cmd_provider, 'echo \'{"foo": "bar"}\'');
        $json = $reflex->getProperty('as_json');
        $json->setAccessible(true);
        $json->setValue($cmd_provider, true);
        $this->assertEquals(
            array('foo' => 'bar'),
            $cmd_provider->getFacts()
        );
    }

    public function testInvalidJsonResults()
    {
        $cmd_provider = new CommandFactsProvider();
        $reflex = new \ReflectionClass($cmd_provider);
        $cmd = $reflex->getProperty('cmd');
        $cmd->setAccessible(true);
        $cmd->setValue($cmd_provider, 'echo \'{"foo" "bar"}\'');
        $json = $reflex->getProperty('as_json');
        $json->setAccessible(true);
        $json->setValue($cmd_provider, true);
        $this->assertEquals(
            array(),
            $cmd_provider->getFacts()
        );
    }

    public function testUnknownCommand()
    {
        $cmd_provider = new CommandFactsProvider();
        $reflex = new \ReflectionClass($cmd_provider);
        $cmd = $reflex->getProperty('cmd');
        $cmd->setAccessible(true);
        $cmd->setValue($cmd_provider, 'myunknwoncommandfortests');
        $this->assertEquals(array(), $cmd_provider->getFacts());
    }
}
