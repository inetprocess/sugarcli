<?php

namespace SugarCli\Tests\Inventory\Facter;

use SugarCli\Inventory\Facter\SugarFacter;
use Inet\SugarCRM\Application;
use Psr\Log\NullLogger;

class SugarFacterTest extends \PHPUnit_Framework_TestCase
{
    public function testVersionProvider()
    {
        $facter = new SugarFacter(new Application(new NullLogger(), __DIR__ . '/../fake_sugar'));
        $facts = $facter->getFacts();
        $this->assertEquals(array(
            'version' => '7.5.0.1',
            'db_version' => '7.5.0.1',
            'flavor' => 'PRO',
            'build' => '1006',
            'build_timestamp' => '2014-12-12 09:59am',
        ), $facts);
    }
}
