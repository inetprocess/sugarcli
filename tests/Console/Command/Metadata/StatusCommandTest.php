<?php

namespace SugarCli\Tests\Console\Command\Metadata;

use Symfony\Component\Console\Tester\CommandTester;

use SugarCli\Console\Application;
use SugarCli\Util\TestLogger;

/**
 * @group sugarcrm-db
 * @group sugarcrm-path
 */
class StatusCommandTest extends MetadataTestCase
{

    public function testStatus()
    {
        $cmd = $this->app->find('metadata:status');
        $tester = new CommandTester($cmd);
        $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__.'/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename(MetadataTestCase::METADATA_NEW)
            )
        );

        $add = 'add: Accounts.test2_c';
        $del = 'delete: inet_ImportCSV.csv_fields_enclosure_c';
        $update = "modified: inet_ImportCSV.protocolsoap_function_dropdown_c ";
        $update .= "{ required: '0', date_modified: '2014-05-16 18:50:56', audited: '1' }";
        $this->assertRegExp("/$add/", $tester->getDisplay());
        $this->assertRegExp("/$del/", $tester->getDisplay());
        $this->assertRegExp("/$update/", $tester->getDisplay());
    }

    public function testQuietStatus()
    {
        $cmd = $this->app->find('metadata:status');
        $tester = new CommandTester($cmd);

        $ret = $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__.'/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename(MetadataTestCase::METADATA_NEW),
                '--quiet' => null,
            )
        );

        $this->assertEquals(2, $ret);

        // Test empty write*
        $func_names = array('writeAdd', 'writeDel', 'writeUpdate');
        $output = $tester->getOutput();
        $reflex = new \ReflectionClass($cmd);
        foreach ($func_names as $func_name) {
            $method = $reflex->getMethod($func_name);
            $method->setAccessible(true);
            $this->assertNull($method->invoke($cmd, $output, array()));
        }
    }

    public function testMissingMetadataFile()
    {
        $logger = $this->app->getContainer()->get('logger');
        $cmd = $this->app->find('metadata:status');
        $tester = new CommandTester($cmd);
        $ret = $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__.'/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename('unknown_file')
            )
        );

        $expected_log = '[error] Unable to access metadata file ' . $this->getYamlFilename('unknown_file') . ".\n";
        $this->assertEquals($expected_log, $logger->getLines());
        $this->assertEquals(21, $ret);

    }

    public function testWringSugarFolder()
    {
        $logger = $this->app->getContainer()->get('logger');
        $cmd = $this->app->find('metadata:status');
        $tester = new CommandTester($cmd);
        $ret = $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__.'/unknown_sugar',
                '--metadata-file' => $this->getYamlFilename(MetadataTestCase::METADATA_NEW)
            )
        );

        $this->assertEquals(20, $ret);

    }

    public function testUpdateOnly()
    {
        $cmd = $this->app->find('metadata:status');
        $tester = new CommandTester($cmd);
        $ret = $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__.'/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename('metadata_update'),
                '--quiet' => null,
            )
        );
        $this->assertEquals(2, $ret);
    }

    public function testNoMetadata()
    {
        $cmd = $this->app->find('metadata:status');
        $tester = new CommandTester($cmd);
        $ret = $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__.'/metadata/fake_sugar',
                '--quiet' => null,
            )
        );
        $this->assertEquals(21, $ret);
    }

    /**
     * @expectedException Inet\SugarCRM\Exception\SugarException
     */
    public function testGetDisplayNameFail()
    {
        $cmd = $this->app->find('metadata:status');
        $cmd->getFieldDisplayName(array('foo'));
    }

    /**
     * @expectedException Inet\SugarCRM\Exception\SugarException
     */
    public function testGetDisplayNameFail2()
    {
        $cmd = $this->app->find('metadata:status');
        $cmd->getFieldDisplayName(array('name' => ''));
    }
}
