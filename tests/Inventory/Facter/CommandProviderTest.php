<?php

namespace SugarCli\Tests\Inventory\Facter;

use SugarCli\Inventory\Facter\CommandProvider;

class CommandProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testFlatResults()
    {
        $cmd_provider = new CommandProvider('echo "foo=bar"');
        $this->assertEquals(
            array('foo' => 'bar'),
            $cmd_provider->getFacts()
        );
    }

    public function testJsonResults()
    {
        $cmd_provider = new CommandProvider('echo \'{"foo": "bar"}\'', true);
        $this->assertEquals(
            array('foo' => 'bar'),
            $cmd_provider->getFacts()
        );
    }

    public function testInvalidJsonResults()
    {
        $cmd_provider = new CommandProvider('echo \'{"foo" "bar"}\'', true);
        $this->assertEquals(
            array(),
            $cmd_provider->getFacts()
        );
    }

    public function testUnknownCommand()
    {
        $cmd_provider = new CommandProvider('myunknwoncommandfortests');
        $this->assertEquals(array(), $cmd_provider->getFacts());
    }
}
