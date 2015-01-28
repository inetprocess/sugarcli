<?php

namespace SugarCli\Console\Command;

require_once(__DIR__ . '/MetadataTestCase.php');

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Console\Application;
use SugarCli\Sugar\TestCase;

class MetadataDumpCommandTest extends MetadataTestCase
{
    /**
     * @group db
     */
    public function testDump()
    {
        $test_dump_yaml = __DIR__ . '/metadata/test_dump.yaml';
        $fsys = new Filesystem();
        $fsys->copy($this->getYamlFilename(MetadataTestCase::METADATA_NEW), $test_dump_yaml, true);

        $app = new Application();
        $app->configure();
        $cmd = $app->find('metadata:dump');
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
}

