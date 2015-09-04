<?php

namespace SugarCli\Tests\Inventory\Facter;

use SugarCli\Inventory\Facter\SystemFacter;

class SystemFacterTest extends \PHPUnit_Framework_TestCase
{

    public function testRegisterProvider()
    {
        $facter = new SystemFacter();
        $this->assertTrue(class_exists('SugarCli\Inventory\Facter\SystemProvider\Hostname'));
    }

    public function testgetFacts()
    {
        $facter = new SystemFacter();
        $facts = $facter->getFacts();
        $this->assertArrayHasKey('hostname', $facts);
    }
}
