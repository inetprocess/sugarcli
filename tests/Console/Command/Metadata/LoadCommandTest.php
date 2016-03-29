<?php

namespace SugarCli\Tests\Console\Command\Metadata;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Util\TestLogger;

/**
 * @group sugarcrm-db
 * @group sugarcrm-path
 */
class LoadCommandTest extends MetadataTestCase
{
    protected $commandTester = null;

    public function setUp()
    {
        parent::setUp();
        $cmd = $this->app->find('metadata:load');
        $this->commandTester = new CommandTester($cmd);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->commandTester = null;
    }

    public function testLoad()
    {
        $this->commandTester->execute(
            array(
                'command' => 'metadata:load',
                '--path' => __DIR__ . '/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename(MetadataTestCase::METADATA_NEW),
            )
        );

        $queryTable = $this->getConnection()
            ->createQueryTable('fields_meta_data', 'SELECT * FROM fields_meta_data ORDER BY id ASC');

        $this->assertTablesEqual($this->getDataSet()->getTable('fields_meta_data'), $queryTable);
    }

    public function testSql()
    {

        $this->commandTester->execute(
            array(
                'command' => 'metadata:load',
                '--path' => __DIR__ . '/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename(MetadataTestCase::METADATA_NEW),
                '--sql' => null,
            )
        );

        //@codingStandardsIgnoreStart
        $expected_sql = <<<EOS
INSERT INTO `fields_meta_data` (`id`, `name`, `vname`, `comments`, `help`, `custom_module`, `type`, `len`, `required`, `default_value`, `date_modified`, `deleted`, `audited`, `massupdate`, `duplicate_merge`, `reportable`, `importable`, `ext1`, `ext2`, `ext3`, `ext4`) VALUES ('Accountstest2_c', 'test2_c', 'LBL_TEST', '', '', 'Accounts', 'varchar', '255', '0', '', '2014-06-04 11:28:08', '0', '0', '0', '0', '1', 'true', '', '', '', '');
DELETE FROM `fields_meta_data` WHERE id = 'inet_ImportCSVcsv_fields_enclosure_c';
UPDATE `fields_meta_data` SET `required` = '0', `date_modified` = '2014-05-16 18:50:56', `audited` = '1' WHERE id = 'inet_ImportCSVprotocolsoap_function_dropdown_c';

No action done. Use --force to execute the queries.

EOS;
        //@codingStandardsIgnoreStop
        $this->assertEquals($expected_sql, $this->commandTester->getDisplay());
    }

    public function testForce()
    {
        $this->commandTester->execute(
            array(
                'command' => 'metadata:load',
                '--path' => __DIR__ . '/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename(MetadataTestCase::METADATA_NEW),
                '--force' => null,
            )
        );

        $expected = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            $this->getYamlFilename(MetadataTestCase::DB_NEW_DATASET)
        );

        $queryTable = $this->getConnection()
            ->createQueryTable('fields_meta_data', 'SELECT * FROM fields_meta_data ORDER BY BINARY id ASC');

        $this->assertTablesEqual($expected->getTable('fields_meta_data'), $queryTable);
    }

    public function testWrongMetadataFile()
    {
        $logger = $this->app->getContainer()->get('logger');
        $ret = $this->commandTester->execute(
            array(
                'command' => 'metadata:load',
                '--path' => __DIR__ . '/metadata/fake_sugar',
                '--metadata-file' => $this->getYamlFilename('unknown_file'),
            )
        );
        $expected_log = '[error] Unable to access metadata file ' . $this->getYamlFilename('unknown_file') . ".\n";
        $this->assertEquals($expected_log, $logger->getLines());
        $this->assertEquals(21, $ret);

    }

    public function testWrongSugarDir()
    {
        $logger = $this->app->getContainer()->get('logger');
        $ret = $this->commandTester->execute(
            array(
                'command' => 'metadata:load',
                '--path' => __DIR__ . '/unknown_sugar',
                '--metadata-file' => $this->getYamlFilename(MetadataTestCase::METADATA_NEW),
            )
        );
        $this->assertEquals(20, $ret);
    }
}
