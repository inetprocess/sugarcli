<?php
namespace SugarCli\Tests\Console\Command\Backup;

use SugarCli\Tests\Console\Command\CommandTestCase;
use Symfony\Component\Process\ProcessBuilder;

class DumpFilesCommandTest extends CommandTestCase
{
    public static $cmd_name = 'backup:dump:files';

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid compression format 'foo'.
     */
    public function testInvalidCompression()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--compression' => 'foo',
                '--path' => __DIR__,
                '--prefix' => 'test',
            ));
    }

    public function testNotExtracted()
    {
        $ret = $this->getCommandTester(self::$cmd_name)
            ->execute(array(
                '--path' => __DIR__,
                '--prefix' => 'test',
            ));
        $this->assertEquals(11, $ret);
    }

    public function commandLineProvider()
    {
        $prefix = "'tar' '--create' '--file=%a' '--directory=".__DIR__."' ";
        $base = 'fake sugar';
        return array(
            // Test case 1
            array($prefix . "'--gzip' '$base'", array()),
            // Test case 2
            array($prefix . "'--gzip' '$base'", array('-c' => 'gzip')),
            // Test case 3
            array($prefix . "'--bzip2' '$base'", array('-c' => 'bzip2')),
            // Test case 4
            array($prefix . "'--gzip' '--exclude' '$base/cache' '$base'", array('-C' => null)),
            // Test case 5
            array(
                $prefix . "'--gzip' '--exclude' '$base/upload/????????-????-????-????-????????????*' '$base'",
                array('-U' => null),
            ),
        );
    }

    /**
     * @dataProvider commandLineProvider
     */
    public function testCommandLine($expected_cmd, $args)
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array_merge(array(
            '--path' => __DIR__.'/fake sugar',
            '--prefix' => 'test',
            '--dry-run' => null,
        ), $args));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat($expected_cmd, rtrim($cmd->getDisplay(), "\n"));
    }

    public function makeTarList(array $paths)
    {
        $list = array();
        foreach ($paths as $path) {
            $elems = explode('/', $path);
            $build = '';
            foreach ($elems as $node) {
                $build .= $node;
                if ($build != $path) {
                    $build .= '/';
                }
                $list[] = $build;
            }
        }
        return implode("\n", array_unique($list));
    }

    public function tarFilesProvider()
    {
        return array(
            //Test case 1
            array(
                $this->makeTarList(array(
                    'fake sugar/cache/cache_file',
                    'fake sugar/ok space/cache/ok_cache_file',
                    'fake sugar/ok space/ok_file',
                    'fake sugar/sugar_version.php',
                    'fake sugar/upload/0340741c-f9e5-11e6-8f65-720c59d7f345.jpeg',
                    'fake sugar/upload/0340741c-f9e5-11e6-8f65-720c59d7f43c',
                    'fake sugar/upload/upload_file',
                )),
                array(),
            ),
            //Test case 2
            array(
                $this->makeTarList(array(
                    'fake sugar/ok space/cache/ok_cache_file',
                    'fake sugar/ok space/ok_file',
                    'fake sugar/sugar_version.php',
                    'fake sugar/upload/0340741c-f9e5-11e6-8f65-720c59d7f345.jpeg',
                    'fake sugar/upload/0340741c-f9e5-11e6-8f65-720c59d7f43c',
                    'fake sugar/upload/upload_file',
                )),
                array('-C' => null),
            ),
            //Test case 3
            array(
                $this->makeTarList(array(
                    'fake sugar/cache/cache_file',
                    'fake sugar/ok space/cache/ok_cache_file',
                    'fake sugar/ok space/ok_file',
                    'fake sugar/sugar_version.php',
                    'fake sugar/upload/upload_file',
                )),
                array('-U' => null),
            ),
            //Test case 4
            array(
                $this->makeTarList(array(
                    'fake sugar/ok space/cache/ok_cache_file',
                    'fake sugar/ok space/ok_file',
                    'fake sugar/sugar_version.php',
                    'fake sugar/upload/upload_file',
                )),
                array('-C' => null, '-U' => null),
            ),
        );
    }

    /**
     * @dataProvider tarFilesProvider
     */
    public function testTarFiles($expected, $args)
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array_merge(array(
            '--path' => __DIR__.'/fake sugar',
            '--prefix' => 'test',
            '--destination-dir' => __DIR__ . '/backup dir',
        ), $args));
        $this->assertEquals(0, $ret);
        $matches = array();
        preg_match('/^SugarCRM files backed up in archive \'(.*)\'.*$/', $cmd->getDisplay(), $matches);
        $archive_file = $matches[1];
        $this->assertFileExists($archive_file);

        $tar = new ProcessBuilder(array(
            'tar',
            '-taf',
            $archive_file,
        ));
        $tar_list = $tar->getProcess()->mustRun()->getOutput();
        $tar_list = explode("\n", rtrim($tar_list, "\n"));
        sort($tar_list);
        unlink($archive_file);
        $this->assertEquals($expected, implode("\n", $tar_list));
    }
}
