<?php

namespace SugarCli\Tests\Inventory\Facter;

use Psr\Log\NullLogger;
use Inet\SugarCRM\Application;
use Inet\SugarCRM\Database\SugarPDO;

use SugarCli\Inventory\Facter\SugarFacter;
use SugarCli\Inventory\Facter\SugarProvider\Version;
use SugarCli\Tests\TestsUtil\MockPDO;

class SugarFacterTest extends \PHPUnit_Framework_TestCase
{
    public function testVersionProvider()
    {
        $provider = new Version(new Application(new NullLogger(), __DIR__ . '/../fake_sugar'), new MockPDO());
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
            new Application(new NullLogger(), __DIR__ . '/../fake_sugar'),
            new MockPDO()
        );
        $facts = $provider->getFacts();
        $this->assertArrayHasKey('instance_id', $facts);
        $this->assertRegExp('/\w+@' . gethostname() . '/', $facts['instance_id']);
    }

    public function testConfigProvider()
    {
        $provider = new \SugarCli\Inventory\Facter\SugarProvider\Config(
            new Application(new NullLogger(), __DIR__ . '/../fake_sugar'),
            new MockPDO()
        );
        $facts = $provider->getFacts();
        $this->assertEquals(array(
            'url' => 'XXXXXXXXXXXXXXX',
            'unique_key' => '9b4af07fd8b49289db29eacb326d8766',
            'log_level' => 'fatal',
        ), $facts);
    }

    public function testGitProvider()
    {
        $provider = new \SugarCli\Inventory\Facter\SugarProvider\Git(
            new Application(new NullLogger(), __DIR__),
            new MockPDO()
        );
        $facts = $provider->getFacts();
        $this->assertArrayHasKey('git', $facts);
        $this->assertArrayHasKey('tag', $facts['git']);
        $this->assertArrayHasKey('branch', $facts['git']);
        $this->assertArrayHasKey('origin', $facts['git']);
        $this->assertArrayHasKey('modified_files', $facts['git']);
        $this->assertInternalType('integer', $facts['git']['modified_files']);
    }

    public function testGitProviderFailures()
    {
        $provider = new \SugarCli\Inventory\Facter\SugarProvider\Git(
            new Application(new NullLogger(), __DIR__ .'/../../../..'),
            new MockPDO()
        );
        $this->assertNull($provider->getModifiedFiles());
        $reflex = new \ReflectionClass($provider);
        $method = $reflex->getMethod('execOrNull');
        $method->setAccessible(true);
        $this->assertNull($method->invoke($provider, 'git status'));
        $this->assertEquals(array(), $provider->getFacts());
    }

    /**
     * @group sugar
     */
    public function testUserInfo()
    {
        $app = new Application(new NullLogger(), getenv('SUGARCLI_SUGAR_PATH'));
        $facter = new \SugarCli\Inventory\Facter\SugarProvider\UsersInfo($app, new SugarPDO($app));
        $facts = $facter->getFacts();
        $this->assertArrayHasKey('active', $facts['users']);
        $this->assertGreaterThan(0, $facts['users']['active']);
        $this->assertArrayHasKey('admin', $facts['users']);
        $this->assertGreaterThan(0, $facts['users']['admin']);
        $this->assertArrayHasKey('last_session', $facts['users']);
    }

    /**
     * @group sugar
     */
    public function testSugarFacter()
    {
        $app = new Application(new NullLogger(), getenv('SUGARCLI_SUGAR_PATH'));
        $facter = new SugarFacter($app, new SugarPDO($app));
        $facts = $facter->getFacts();
        $this->assertArrayHasKey('instance_id', $facts);
        $this->assertArrayHasKey('version', $facts);
    }
}
