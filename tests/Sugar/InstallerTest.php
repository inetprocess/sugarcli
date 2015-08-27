<?php

namespace SugarCli\Tests\Sugar;

// TODO: Test for callUrl

use SugarCli\Sugar\Installer;
use SugarCli\Tests\TestsUtil\TestLogger;

class InstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigSiCopy()
    {
        $fake_sugar = __DIR__ . '/fake_sugar';
        $config_path = __DIR__ . '/installer/config_si.php';
        $installer = new Installer($fake_sugar, '', '', $config_path, new TestLogger());
        $installer->copyConfigSi();
        $this->assertFileEquals($config_path, $fake_sugar . '/config_si.php');
        $installer->deleteConfigSi();
        $this->assertFileNotExists($fake_sugar . '/config_si.php');
    }

    public function testPathManipulation()
    {
        $new_path = __DIR__ . '/new_sugar';
        $installer = new Installer($new_path, '', '', '', new TestLogger());
        $installer->createPath();
        $this->assertFileExists($new_path);
        $installer->deletePath();
        $this->assertFileNotExists($new_path);
    }

    /**
     * @dataProvider junkParentProvider
     */
    public function testJunkParent($expected, $path)
    {
        $this->assertEquals($expected, Installer::junkParent($path));
    }

    public function junkParentProvider()
    {
        return array(
            array('soap.php', 'SugarPro-Full-7.2.1/soap.php'),
            array('foo/bar/soap.php', 'SugarPro-Full-7.2.1/foo/bar/soap.php'),
            array('foo/bar/soap.php', '/SugarPro-Full-7.2.1/foo/bar/soap.php'),
            array('', '/SugarPro-Full-7.2.1/'),
        );
    }

    /**
     * @expectedException SugarCli\Sugar\InstallerException
     * @expectedExceptionMessageRegExp /is not a directory/
     */
    public function testExtractNotEmpty()
    {
        $installer = new Installer(__DIR__, '', '', '', new TestLogger());
        $installer->extract();
    }

    /**
     * @expectedException SugarCli\Sugar\InstallerException
     * @expectedExceptionMessageRegExp /is not a directory/
     */
    public function testExtractNotDir()
    {
        $installer = new Installer(__FILE__, '', '', '', new TestLogger());
        $installer->extract();
    }

    /**
     * @expectedException SugarCli\Sugar\InstallerException
     * @expectedExceptionMessageRegExp /doesn't exists/
     */
    public function testExtractInvalidSource()
    {
        $installer = new Installer(__DIR__ . '/empty', '', '', '', new TestLogger());
        $installer->createPath();
        $installer->extract();
    }

    /**
     * @expectedException SugarCli\Sugar\InstallerException
     * @expectedExceptionMessageRegExp /Unable to open zip/
     */
    public function testExtractInvalidZip()
    {
        $installer = new Installer(__DIR__ . '/empty', '', __FILE__, '', new TestLogger());
        $installer->createPath();
        $installer->extract();
    }

    public function testExtract()
    {
        $installer = new Installer(__DIR__ . '/empty', '', __DIR__ . '/installer/Fake_Sugar.zip', '', new TestLogger());
        $installer->createPath();
        $installer->extract();
        $this->assertFileExists(__DIR__ . '/empty/sugar_version.php');
        $installer->deletePath();
    }

    /**
     * Stub the call to the url.
     */
    public function testRun()
    {
        $new_path = __DIR__ . '/install_sugar';
        $installer_dir = __DIR__ . '/installer';
        $stub = $this->getMock(
            'SugarCli\Sugar\Installer',
            array('callUrl'),
            array(
                $new_path,
                '',
                $installer_dir . '/Fake_Sugar.zip',
                $installer_dir . '/config_si.php',
                new TestLogger()
            )
        );
        $stub->deletePath();
        $stub->run();
        $this->assertFileExists($new_path . '/sugar_version.php');
        unlink($new_path . '/sugar_version.php');
        $this->assertFileNotExists($new_path . '/sugar_version.php');
        $stub->run(true);
        $this->assertFileExists($new_path . '/sugar_version.php');
    }

    /**
     * @expectedException SugarCli\Sugar\InstallerException
     * @expectedExceptionMessageRegExp /Use --force/
     */
    public function testFailedRun()
    {
        $new_path = __DIR__ . '/install_sugar';
        $installer_dir = __DIR__ . '/installer';
        $installer = new Installer(
            $new_path,
            '',
            $installer_dir . '/Fake_Sugar.zip',
            $installer_dir . '/config_si.php',
            new TestLogger()
        );
        $installer->run();
    }

    /**
     * @expectedException SugarCli\Sugar\InstallerException
     * @expectedExceptionMessageRegExp /Missing or unreadable config_si/
     */
    public function testFailedRunInvalidConfigSi()
    {
        $installer = new Installer(
            '',
            '',
            '',
            '',
            new TestLogger()
        );
        $installer->run();
    }
}
