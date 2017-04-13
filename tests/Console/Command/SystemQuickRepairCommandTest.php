<?php

namespace SugarCli\Tests\Console\Command;

use Inet\SugarCRM\Application as SugarApp;
use SugarCli\Console\Command\SystemQuickRepairCommand;
use SugarCli\Tests\Console\Command\CommandTestCase;
use SugarCli\Tests\TestsUtil\TestLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group sugarcrm-path
 */
class SystemQuickRepairCommandTest extends CommandTestCase
{

    public function testOldSugar()
    {
        $cmd = $this->getApplication()->find('system:quickrepair');
        $logger = new TestLogger();
        $sugar_app = new SugarApp($logger, __DIR__.'/repair/old_sugar');

        $cache_path =  __DIR__.'/repair/old_sugar/cache';
        $fs = new Filesystem();
        $fs->mkdir($cache_path);
        $fs->touch($cache_path . '/test.txt');
        // This should not remove the cache because the app is too old
        $cmd->removeCache($sugar_app);
        $this->assertFileExists($cache_path . '/test.txt');
    }

    public function testRemoveCache()
    {
        $cmd = $this->getApplication()->find('system:quickrepair');
        $logger = new TestLogger();
        $sugar_app = new SugarApp($logger, __DIR__.'/repair/fake_sugar');

        $cache_path =  __DIR__.'/repair/fake_sugar/cache';
        $fs = new Filesystem();
        $fs->mkdir($cache_path);
        $fs->touch($cache_path . '/test.txt');
        // This should remove the cache
        $cmd->removeCache($sugar_app);
        $this->assertFileNotExists($cache_path);
    }

    /**
     * @group sugarcrm-slow
     */
    public function testRepair()
    {
        $cmd = $this->getCommandTester('system:quickrepair');

        $checkFile = getenv('SUGARCLI_SUGAR_PATH') . '/cache/class_map.php';
        $this->assertFileExists($checkFile, 'That file is used to test my repair');
        unlink($checkFile);
        $this->assertFileNotExists($checkFile, 'That file is used to test my repair');
        $result = $cmd->execute(
            array('--path' => getenv('SUGARCLI_SUGAR_PATH')),
            array('verbosity' => OutputInterface::VERBOSITY_VERBOSE)
        );
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertNotEmpty($output);
        $this->assertFileExists($checkFile, 'That file is used to test my repair');
    }
}
