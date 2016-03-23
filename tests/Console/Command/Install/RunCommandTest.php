<?php
namespace SugarCli\Tests\Console\Command\Install;

use Symfony\Component\Filesystem\Filesystem;
use SugarCli\Tests\Console\Command\CommandTestCase;

/**
 * @group sugarcrm-url
 * @group sugarcrm-path
 */
class RunCommandTest extends CommandTestCase
{
    public function testFailedRun()
    {
        $ret = $this->getCommandTester('install:run')
            ->execute(array(
                '--path' => '',
                '--source' => '',
                '--config' => '',
                '--url' => '',
            ));
        $this->assertEquals(13, $ret);
    }

    public function testFakeRun()
    {
        $this->assertFileExists(
            getenv('SUGARCLI_SUGAR_PATH'),
            'Please specify the SUGARCLI_SUGAR_PATH from the environment or phpunit.xml file.'
        );
        $this->assertNotEmpty(
            getenv('SUGARCLI_SUGAR_URL'),
            'Please specify the SUGARCLI_SUGAR_URL from the environment or phpunit.xml file.'
        );
        $install_path = getenv('SUGARCLI_SUGAR_PATH') . '/inetprocess_installer';
        $install_url = getenv('SUGARCLI_SUGAR_URL') . '/inetprocess_installer';
        $fs = new Filesystem;
        if ($fs->exists($install_path)) {
            $fs->remove($install_path);
        }
        $fs->mkdir($install_path);
        $ret = $this->getCommandTester('install:run')
            ->execute(array(
                '--path' => $install_path,
                '--source' => __DIR__ . '/installer/Fake_Sugar.zip',
                '--config' => __DIR__ . '/installer/config_si.php',
                '--url' => $install_url,
            ));
        $this->assertEquals(0, $ret);
        $this->assertFileExists($install_path . '/config.php');
        $fs->remove($install_path);
    }

    public function testFakeRunWithoutUrl()
    {
        $this->assertFileExists(
            getenv('SUGARCLI_SUGAR_PATH'),
            'Please specify the SUGARCLI_SUGAR_PATH from the environment or phpunit.xml file.'
        );
        $this->assertNotEmpty(
            getenv('SUGARCLI_SUGAR_URL'),
            'Please specify the SUGARCLI_SUGAR_URL from the environment or phpunit.xml file.'
        );
        $install_path = getenv('SUGARCLI_SUGAR_PATH') . '/inetprocess_installer';
        $install_url = getenv('SUGARCLI_SUGAR_URL') . '/inetprocess_installer';
        $fs = new Filesystem;
        if ($fs->exists($install_path)) {
            $fs->remove($install_path);
        }
        $fs->mkdir($install_path);
        $config_si_file = __DIR__ . '/installer/config_url_si.php';
        $config_si = file_get_contents(__DIR__ . '/installer/config_si.php');
        $config_si = str_replace('<SITE_URL>', $install_url, $config_si);
        file_put_contents($config_si_file, $config_si);
        $ret = $this->getCommandTester('install:run')
            ->execute(array(
                '--path' => $install_path,
                '--source' => __DIR__ . '/installer/Fake_Sugar.zip',
                '--config' => $config_si_file,
            ));
        $this->assertEquals(0, $ret);
        $this->assertFileExists($install_path . '/config.php');
        $fs->remove($install_path);
        $fs->remove($config_si_file);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /The config file ".*" is not readable\./
     */
    public function testRunWithoutUrlMissingConfigFile()
    {
        $ret = $this->getCommandTester('install:run')
            ->execute(array(
                '--path' => '/invalid',
                '--source' => __DIR__ . '/installer/Fake_Sugar.zip',
                '--config' => __DIR__ . '/installer/unknown_file.php',
            ));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /"setup_site_url" is not set in configuration file./
     */
    public function testRunWithoutUrlInvalidConfigFile()
    {
        $ret = $this->getCommandTester('install:run')
            ->execute(array(
                '--path' => '/invalid',
                '--source' => __DIR__ . '/installer/Fake_Sugar.zip',
                '--config' => __DIR__ . '/installer/invalid_config.php',
            ));
    }
}
