<?php
namespace SugarCli\Tests\Console\Command\Backup;

use SugarCli\Tests\Console\Command\CommandTestCase;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Filesystem\Filesystem;

class RestoreFilesCommandTest extends CommandTestCase
{
    public static $cmd_name = 'backup:restore:files';

    public function getSugarPath()
    {
        return __DIR__.'/fake sugar';
    }

    public function getBackupDir()
    {
        return __DIR__.'/backup dir';
    }

    public function getArchiveFile()
    {
        return $this->getBackupDir() . '/phpunit_ignore_all.tar.gz';
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Archive file "UNKNOWN" not found
     */
    public function testInvalidArchive()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => $this->getSugarPath(),
                '--archive' => 'UNKNOWN',
                '--dry-run' => null,
            ));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid compression format 'foo'.
     */
    public function testInvalidCompression()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--compression' => 'foo',
                '--path' => $this->getSugarPath(),
                '--archive' => __DIR__,
                '--dry-run' => null,
            ));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Could not guess compression. Please set the --compression option.
     */
    public function testCompressionNotGuessed()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => $this->getSugarPath(),
                '--archive' => __DIR__,
                '--dry-run' => null,
            ));
    }

    public function commandLineProvider()
    {
        $prefix = "'tar' '--extract' '--strip-components=1' '--file=".$this->getArchiveFile()."'"
           ." '--directory=" . __DIR__ . "' ";
        return array(
            // Test case 1
            array($prefix . "'--gzip'", array()),
            // Test case 2
            array($prefix . "'--bzip2'", array('--compression' => 'bzip2')),
        );
    }

    /**
     * @dataProvider commandLineProvider
     */
    public function testCommandLine($expected_cmd, $args)
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array_merge(array(
            '--path' => __DIR__,
            '--archive' => $this->getArchiveFile(),
            '--dry-run' => null,
        ), $args));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat($expected_cmd, rtrim($cmd->getDisplay(), "\n"));
    }

    public function testExtract()
    {
        $extract_dir = __DIR__. '/extract dir/';
        $extract_orig_dir = __DIR__.'/extract dir.orig';
        $fs = new Filesystem();
        //Setup
        $fs->remove(array($extract_dir, $extract_orig_dir));
        $this->assertFileNotExists($extract_dir);
        $this->assertFileNotExists($extract_orig_dir);
        $fs->mkdir($extract_dir);
        $fs->touch($extract_dir . '/test');
        $this->assertFileExists($extract_dir);
        $this->assertFileExists($extract_dir.'/test');

        //Execute
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => $extract_dir,
            '--archive' => $this->getArchiveFile(),
        ));
        $this->assertEquals(0, $ret);
        /* $this->assertStringMatchesFormat($expected_cmd, rtrim($cmd->getDisplay(), "\n")); */
        $this->assertFileExists($extract_dir);
        $this->assertFileExists($extract_dir . '/sugar_version.php');
        $this->assertFileNotExists($extract_dir . '/test');
        $this->assertFileExists($extract_orig_dir);
        $this->assertFileExists($extract_orig_dir.'/test');

        // Cleanup
        $fs->remove(array($extract_dir, $extract_orig_dir));
        $this->assertFileNotExists($extract_dir);
        $this->assertFileNotExists($extract_orig_dir);
    }

    public function testOverwrite()
    {
        $extract_dir = __DIR__. '/extract dir/';
        $extract_orig_dir = __DIR__.'/extract dir.orig';
        $fs = new Filesystem();
        //Setup
        $fs->remove(array($extract_dir, $extract_orig_dir));
        $this->assertFileNotExists($extract_dir);
        $this->assertFileNotExists($extract_orig_dir);
        $fs->mkdir($extract_dir);
        $fs->touch($extract_dir . '/test');
        $this->assertFileExists($extract_dir);
        $this->assertFileExists($extract_dir.'/test');

        //Execute
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => $extract_dir,
            '--archive' => $this->getArchiveFile(),
            '--overwrite' => null,
        ));
        $this->assertEquals(0, $ret);
        /* $this->assertStringMatchesFormat($expected_cmd, rtrim($cmd->getDisplay(), "\n")); */
        $this->assertFileExists($extract_dir);
        $this->assertFileExists($extract_dir . '/sugar_version.php');
        $this->assertFileExists($extract_dir . '/test');
        $this->assertFileNotExists($extract_orig_dir);

        // Cleanup
        $fs->remove(array($extract_dir, $extract_orig_dir));
        $this->assertFileNotExists($extract_dir);
        $this->assertFileNotExists($extract_orig_dir);
    }
}
