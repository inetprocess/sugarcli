<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

use SugarCli\Console\Application;

class MetadataStatusCommandTest extends \PHPUnit_Extensions_Database_TestCase
{

    public function getConnection()
    {
        $dsn = 'mysql:';
        $params[] = 'host=' . (empty($GLOBALS['TEST_DB_HOST']) ? 'localhost' : $GLOBALS['TEST_DB_HOST']);
        if (!empty($GLOBALS['TEST_DB_PORT'])) {
            $params[] = 'port=' . $GLOBALS['TEST_DB_PORT'];
        }
        $params[] = 'dbname=' . $GLOBALS['TEST_DB_NAME'];

        $dsn .= implode(';', $params);

        $pdo = new \PDO($dsn, $GLOBALS['TEST_DB_USER'], $GLOBALS['TEST_DB_PASSWORD']);
        return $this->createDefaultDBConnection($pdo, $GLOBALS['TEST_DB_NAME']);
    }

    public function getDataSet()
    {
        $yaml = __DIR__ . '/metadata/db.yaml';
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet($yaml);
    }

    public function setUp()
    {
        parent::setUp();

        $config = file_get_contents(__DIR__ . '/metadata/fake_sugar/config.tpl.php');
        $config = str_replace(array(
                '<DB_USER>',
                '<DB_PASSWORD>',
                '<DB_NAME>'
            ),
            array(
                $GLOBALS['TEST_DB_USER'],
                $GLOBALS['TEST_DB_PASSWORD'],
                $GLOBALS['TEST_DB_NAME'],
            ),
            $config
        );
        file_put_contents(__DIR__ . '/metadata/fake_sugar/config.php', $config);
    }

    public function tearDown()
    {
        parent::tearDown();
        unlink(__DIR__ . '/metadata/fake_sugar/config.php');
    }


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
                '--metadata-file' => __DIR__ . '/metadata/new.yaml'
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

