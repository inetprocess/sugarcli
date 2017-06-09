<?php

namespace SugarCli\Tests\Console\Command\Backup;

use SugarCli\Tests\Console\Command\CommandTestCase;
use Symfony\Component\Filesystem\Filesystem;

class RestoreAllCommandTest extends CommandTestCase
{
    public static $cmd_name = 'backup:restore:all';


    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegexp Could not find a database archive file with the same name as
     */
    public function testNoDumpFile()
    {
        $fs = new Filesystem();
        $archives = array(
            __DIR__ . '/test.tar.gz',
        );
        $fs->touch($archives);
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--archive' => $archives[0],
            '--dry-run' => null,
        ));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat("'tar'%a\n%a'mysql'%a", rtrim($cmd->getDisplay(), "\n"));
        $fs->remove($archives);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Enable to extract dump name from
     */
    public function testNoKnownExtention()
    {
        $fs = new Filesystem();
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--compression' => 'gzip',
            '--archive' => __FILE__,
            '--dry-run' => null,
        ));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat("'tar'%a\n%a'mysql'%a", rtrim($cmd->getDisplay(), "\n"));
    }

    public function testCommandLine()
    {
        $fs = new Filesystem();
        $archives = array(
            __DIR__ . '/test.tar.gz',
            __DIR__ . '/test.sql.gz',
        );
        $fs->touch($archives);
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--archive' => $archives[0],
            '--dry-run' => null,
        ));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat("'tar'%a\n%a.sql.gz'%a'mysql'%a", rtrim($cmd->getDisplay(), "\n"));
        $fs->remove($archives);
    }
}
