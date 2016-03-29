<?php

namespace SugarCli\Console\Command\Inventory;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Application;

class AbstractInventoryCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCustomFacts()
    {
        $mock = $this->getMockForAbstractClass(
            'SugarCli\Console\Command\Inventory\AbstractInventoryCommand',
            array('test')
        );
        $input = new StringInput(
            '--custom-fact="foo:bar" --custom-fact "baz.foo:bar" --custom-fact "baz.test:a"'
        );
        $input->bind($mock->getDefinition());
        $expected = 'bar';
        $this->assertEquals($expected, $mock->getCustomFacts($input, 'foo'));
        $expected = array(
                'foo' => 'bar',
                'test' => 'a',
        );
        $this->assertEquals($expected, $mock->getCustomFacts($input, 'baz'));
        $this->assertEquals(array(), $mock->getCustomFacts($input, 'test'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid format for --custom-fact 'foobar'
     */
    public function testGetCustomFactsInvalid()
    {
        $mock = $this->getMockForAbstractClass(
            'SugarCli\Console\Command\Inventory\AbstractInventoryCommand',
            array('test')
        );
        $input = new ArrayInput(array(
            '--custom-fact' => array(
                'foobar',
            )
        ));
        $input->bind($mock->getDefinition());
        $mock->getCustomFacts($input, '');
    }
}
