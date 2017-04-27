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
                $prefix . "'--gzip' '--exclude' '$base/upload/????????-????-????-????-????????????*'"
                . " '--exclude' '*-restore' '$base'",
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
                    'fake sugar/upload/upgrades/test-restore/foo',
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
                    'fake sugar/upload/upgrades/test-restore/foo',
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
                    'fake sugar/upload/upgrades/',
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
                    'fake sugar/upload/upgrades/',
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

    public function testInvalidDu()
    {
        $reflex = new \ReflectionClass('SugarCli\Console\Command\Backup\DumpFilesCommand');
        $prop = $reflex->getProperty('du_bin');
        $prop->setAccessible(true);
        $old_val = $prop->getValue();
        $prop->setValue('invalid cmd');

        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => __DIR__.'/fake sugar',
            '--prefix' => 'test',
            '--dry-run' => null,
        ));
        $prop->setValue($old_val);
        $this->assertEquals(0, $ret);
        $this->assertEquals(
            '[warning] Command `du` not available. Unable to test size of backup.' .PHP_EOL,
            $this->getApplication()->getContainer()->get('logger')->getLines()
        );

    }

    public function bigDirProvider()
    {
        return array(
            // Test case 0
            array(
                7,
                '[warning] Cache directory "%a" is huge with a size of 1MB.'
                . ' You should consider ignoring this folder with `--ignore-cache`.',
                'Dump files anyway ? [y/N]Backup not run',
                'no',
                '-1',
                null,
            ),
            // Test case 1
            array(
                7,
                '[warning] Upload directory "%a" is huge with a size of 1MB.'
                . ' You should consider ignoring this folder with `--ignore-upload`.',
                'Dump files anyway ? [y/N]Backup not run',
                'no',
                null,
                '-1',
            ),
            // Test case 2
            array(
                7,
                '[warning] Cache directory "%a" is huge with a size of 1MB.'
                . ' You should consider ignoring this folder with `--ignore-cache`.'
                . "\n"
                . '[warning] Upload directory "%a" is huge with a size of 1MB.'
                . ' You should consider ignoring this folder with `--ignore-upload`.',
                'Dump files anyway ? [y/N]Backup not run',
                'no',
                '-1',
                '-1',
            ),
            // Test case 3
            array(
                0,
                '[warning] Cache directory "%a" is huge with a size of 1MB.'
                . ' You should consider ignoring this folder with `--ignore-cache`.'
                . "\n"
                . '[warning] Upload directory "%a" is huge with a size of 1MB.'
                . ' You should consider ignoring this folder with `--ignore-upload`.',
                "Dump files anyway ? [y/N]'tar' '--create'%a",
                'yes',
                '-1',
                '-1',
            ),
            // Test case 4
            array(
                0,
                '',
                "'tar' '--create'%a",
                '',
                '-1',
                '-1',
                array('-C' => null, '-U' => null),
            ),
            // Test case 5
            array(
                0,
                '',
                "'tar' '--create'%a",
                null,
                '-1',
                '-1',
                array('--no-interaction' => null),
            ),
        );
    }

    /**
     * @dataProvider bigDirProvider
     */
    public function testBigDir(
        $expect_ret,
        $expect_log,
        $expect_out,
        $input,
        $cache_size,
        $upload_size,
        $args = array()
    ) {
        $reflex = new \ReflectionClass('SugarCli\Console\Command\Backup\DumpFilesCommand');
        $prop_cache = $reflex->getProperty('cache_dir_max_size');
        $prop_cache->setAccessible(true);
        $old_cache = $prop_cache->getValue();
        if ($cache_size != null) {
            $prop_cache->setValue($cache_size);
        }
        $prop_upload = $reflex->getProperty('upload_dir_max_size');
        $prop_upload->setAccessible(true);
        $old_upload = $prop_upload->getValue();
        if ($upload_size != null) {
            $prop_upload->setValue($upload_size);
        }

        $logger = $this->getApplication()->getContainer()->get('logger');
        $cmd = $this->getCommandTester(self::$cmd_name, $input);
        $ret = $cmd->execute(array_merge(
            array(
                '--path' => __DIR__.'/fake sugar',
                '--prefix' => 'test',
                '--dry-run' => null,
            ),
            $args
        ));
        $prop_cache->setValue($old_cache);
        $prop_upload->setValue($old_upload);
        $this->assertEquals($expect_ret, $ret);
        $this->assertStringMatchesFormat(
            $expect_log,
            $logger->getLines()
        );
        $this->assertStringMatchesFormat(
            $expect_out,
            $cmd->getDisplay()
        );
    }
}
