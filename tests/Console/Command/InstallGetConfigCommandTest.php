<?php
namespace SugarCli\Console\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Util\TestLogger;

class InstallGetConfigCommandTest extends \PHPUnit_Framework_TestCase
{
    public function getTestDir()
    {
        return __DIR__ . '/test_config_si';
    }

    public function getCommandTester()
    {
        $app = new Application();
        $app->getHelperSet()->set(new TestLogger());
        $app->add(new InstallGetConfigCommand());

        $cmd = $app->find('install:config:get');
        return new CommandTester($cmd);
    }

    public function getRessourceConfig()
    {
        return __DIR__ . '/../../../res/config_si.php';
    }

    public function testDefaults()
    {
        $config = $this->getTestDir() . '/config_si.php';

        $old_cwd = getcwd();
        chdir($this->getTestDir());

        $fsys = new Filesystem();
        if ($fsys->exists($config)) {
            $fsys->remove($config);
        }

        $this->getCommandTester()->execute(array());
        chdir($old_cwd);

        $this->assertFileEquals(
            $this->getRessourceConfig(),
            $config
        );

        if ($fsys->exists($config)) {
            $fsys->remove($config);
        }
    }

    public function testConfigOption()
    {
        $config = $this->getTestDir() . '/custom_config_si.php';

        $fsys = new Filesystem();
        if ($fsys->exists($config)) {
            $fsys->remove($config);
        }

        $this->getCommandTester()->execute(array(
            '--config' => $config
        ));

        $this->assertFileEquals(
            $this->getRessourceConfig(),
            $config
        );

        if ($fsys->exists($config)) {
            $fsys->remove($config);
        }

    }

    public function testExistingFile()
    {
        $config = $this->getTestDir() . '/config_si.php';
        $empty_file = $this->getTestDir() . '/empty.php';

        $fsys = new Filesystem();
        $fsys->copy($empty_file, $config, true);

        $ret = $this->getCommandTester()->execute(array(
            '--config' => $config
        ));

        $this->assertFileEquals($empty_file, $config);
        $this->assertEquals(14, $ret);

        if ($fsys->exists($config)) {
            $fsys->remove($config);
        }

    }

    public function testOverwriteFile()
    {
        $config = $this->getTestDir() . '/config_si.php';
        $empty_file = $this->getTestDir() . '/empty.php';

        $fsys = new Filesystem();
        $fsys->copy($empty_file, $config, true);

        $this->getCommandTester()->execute(array(
            '--config' => $config,
            '--force' => null
        ));

        $this->assertFileEquals(
            $this->getRessourceConfig(),
            $config
        );

        if ($fsys->exists($config)) {
            $fsys->remove($config);
        }
    }
}

