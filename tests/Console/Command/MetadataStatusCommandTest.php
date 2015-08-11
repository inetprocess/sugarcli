<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

use SugarCli\Console\Application;
use SugarCli\Util\TestLogger;

class MetadataStatusCommandTest extends MetadataTestCase
{

    /**
     * @group db
     */
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

        $ret = $tester->execute(
            array(
                'command' => $cmd->getName(),
                '--path' => __DIR__.'/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename(MetadataTestCase::METADATA_NEW),
                '--quiet' => null,
            )
        );

        $this->assertEquals(2, $ret);

    }

    /**
     * @group db
     */
    public function testFailure()
    {
        $logger = new TestLogger();
        $this->app->getHelperSet()->set($logger);
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
}
