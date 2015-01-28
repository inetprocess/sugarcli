<?php

namespace SugarCli\Console\Command;

require_once(__DIR__ . '/MetadataTestCase.php');

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Sugar\TestCase;
use SugarCli\Util\TestLogger;

class MetadataLoadCommandTest extends MetadataTestCase
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

    /**
     * @group db
     */
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

    /**
     * @group db
     */
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
INSERT INTO fields_meta_data (id, name, vname, comments, help, custom_module, type, len, required, default_value, date_modified, deleted, audited, massupdate, duplicate_merge, reportable, importable, ext1, ext2, ext3, ext4) VALUES('Accountstest2_c', 'test2_c', 'LBL_TEST', '', '', 'Accounts', 'varchar', '255', '0', 'Accountstest2_c'0, 'Accountstest2_c'1, 'Accountstest2_c'2, 'Accountstest2_c'3, 'Accountstest2_c'4, 'Accountstest2_c'5, 'Accountstest2_c'6, 'Accountstest2_c'7, 'Accountstest2_c'8, 'Accountstest2_c'9, 'test2_c'0, 'test2_c'1);
DELETE FROM fields_meta_data WHERE id = 'inet_ImportCSVcsv_fields_enclosure_c';
UPDATE fields_meta_data SET required = '0', date_modified = '2014-05-16 18:50:56', audited = '1' WHERE id = 'inet_ImportCSVprotocolsoap_function_dropdown_c';

No action done. Use --force to execute the queries.

EOS;
        //@codingStandardsIgnoreStop
        $this->assertEquals($expected_sql, $this->commandTester->getDisplay());
    }

    /**
     * @group db
     */
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

    /**
     * @group db
     */
    public function testFailure()
    {
        $logger = new TestLogger();
        $this->app->getHelperSet()->set($logger);
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
}

