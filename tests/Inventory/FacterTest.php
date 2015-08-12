<?php

namespace SugarCli\Tests\Inventory;

use SugarCli\Inventory\Facter;

class FacterTest extends \PHPUnit_Framework_TestCase
{

    public function testRegisterProvider()
    {
        $facter = new Facter();
        $this->assertTrue(class_exists('SugarCli\Inventory\FactsProvider\Hostname'));
    }

    public function testgetFacts()
    {
        $facter = new Facter();
        $facts = $facter->getFacts();
        $this->assertArrayHasKey('hostname', $facts);
    }
}
