<?php

namespace SugarCli\Console\Command;

require_once(__DIR__ . '/MetadataTestCase.php');

use Symfony\Component\Console\Tester\CommandTester;

use SugarCli\Console\Application;
use SugarCli\Sugar\TestCase;

class MetadataStatusCommandTest extends MetadataTestCase
{

    /**
     * @group db
     */
    public function testStatus()
    {
        $app = new Application();
        $app->configure();
        $cmd = $app->find('metadata:status');
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
}

