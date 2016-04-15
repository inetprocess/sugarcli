<?php
namespace SugarCli\Tests\Console\Command\Install;

use Symfony\Component\Filesystem\Filesystem;
use SugarCli\Tests\Console\Command\CommandTestCase;

/**
 * @group sugarcrm-url
 * @group sugarcrm-path
 * Url parameter is kept to keep testing for backward compatibility.
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
        $install_path = getenv('SUGARCLI_SUGAR_PATH') . '/inetprocess_installer';
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
                '--url' => '',
            ));
        $this->assertEquals(0, $ret);
        $this->assertFileExists($install_path . '/config.php');
        $fs->remove($install_path);
    }
}
