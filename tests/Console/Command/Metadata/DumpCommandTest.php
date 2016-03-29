<?php
namespace SugarCli\Tests\Console\Command\Metadata;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Console\Application;
use SugarCli\Util\TestLogger;

/**
 * @group sugarcrm-db
 * @group sugarcrm-path
 */
class DumpCommandTest extends MetadataTestCase
{
    public function testDump()
    {
        $test_dump_yaml = __DIR__ . '/metadata/test_dump.yaml';
        $fsys = new Filesystem();
        $fsys->copy($this->getYamlFilename(MetadataTestCase::METADATA_NEW), $test_dump_yaml, true);

        $cmd = $this->app->find('metadata:dump');
        $tester = new CommandTester($cmd);
        $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__ . '/metadata/fake_sugar',
                '--metadata-file' => $test_dump_yaml,
            )
        );

        $this->assertFileEquals($this->getYamlFilename(MetadataTestCase::METADATA_BASE), $test_dump_yaml);
        $fsys->remove($test_dump_yaml);
    }

    public function testWithNewFile()
    {
        $test_dump_yaml = __DIR__ . '/metadata/new_file.yaml';
        $logger = $this->app->getContainer()->get('logger');
        $fsys = new Filesystem();

        // Make sure the test file is remove before testing.
        $fsys->remove($test_dump_yaml);

        $cmd = $this->app->find('metadata:dump');
        $tester = new CommandTester($cmd);
        $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__ . '/metadata/fake_sugar',
                '--metadata-file' => $test_dump_yaml,
            )
        );

        $this->assertEquals('', $logger->getLines());
        $this->assertFileEquals($this->getYamlFilename(MetadataTestCase::METADATA_BASE), $test_dump_yaml);

        $fsys->remove($test_dump_yaml);
    }


    public function testUpdateOnly()
    {
        $test_dump_yaml = __DIR__ . '/metadata/test_dump.yaml';
        $fsys = new Filesystem();
        $fsys->copy($this->getYamlFilename('metadata_update'), $test_dump_yaml, true);

        $cmd = $this->app->find('metadata:dump');
        $tester = new CommandTester($cmd);
        $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__ . '/metadata/fake_sugar',
                '--metadata-file' => $test_dump_yaml,
                '--update' => null,
            )
        );

        $this->assertFileEquals($this->getYamlFilename(MetadataTestCase::METADATA_BASE), $test_dump_yaml);
        $fsys->remove($test_dump_yaml);
    }


    public function testFailure()
    {
        $test_dump_yaml = __DIR__ . '/metadata_unknwown_dir/new_file.yaml';
        $logger = $this->app->getContainer()->get('logger');
        $cmd = $this->app->find('metadata:dump');
        $tester = new CommandTester($cmd);
        $ret = $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__ . '/metadata/fake_sugar',
                '--metadata-file' => $test_dump_yaml,
            )
        );

        $expected_log = '[error] An error occured while dumping the metadata.' . PHP_EOL;
        $expected_log .= '[error] Unable to dump metadata file to ' . $test_dump_yaml . ".\n";

        $this->assertEquals($expected_log, $logger->getLines());
        $this->assertEquals(20, $ret);

    }
}
