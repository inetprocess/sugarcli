<?php

namespace SugarCli\Tests\Inventory\Facter;

use SugarCli\Inventory\Facter\SugarFacter;
use SugarCli\Inventory\Facter\SugarProvider\Version;
use Inet\SugarCRM\Application;
use Psr\Log\NullLogger;

class SugarFacterTest extends \PHPUnit_Framework_TestCase
{
    public function testVersionProvider()
    {
        $provider = new Version(new Application(new NullLogger(), __DIR__ . '/../fake_sugar'));
        $facts = $provider->getFacts();
        $this->assertEquals(array(
            'version' => '7.5.0.1',
            'db_version' => '7.5.0.1',
            'flavor' => 'PRO',
            'build' => '1006',
            'build_timestamp' => '2014-12-12 09:59am',
        ), $facts);
    }

    public function testInstanceIdProvider()
    {
        $provider = new \SugarCli\Inventory\Facter\SugarProvider\InstanceId(
            new Application(new NullLogger(), __DIR__ . '/../fake_sugar')
        );
        $facts = $provider->getFacts();
        $this->assertEquals(array(
            'instance_id' => posix_getlogin() . '@' . gethostname()
        ), $facts);
    }

    public function testSugarFacter()
    {
        $facter = new SugarFacter(new Application(new NullLogger(), __DIR__ . '/../fake_sugar'));
        $facts = $facter->getFacts();
        $this->assertArrayHasKey('instance_id', $facts);
        $this->assertArrayHasKey('version', $facts);
    }
}
