<?php

namespace SugarCli\Tests\Console\Command\Metadata;

use SugarCli\Console\Application;
use SugarCli\Tests\TestsUtil\DatabaseTestCase;
use SugarCli\Tests\TestsUtil\TestLogger;

class MetadataTestCase extends DatabaseTestCase
{
    protected $app = null;

    const DB_BASE_DATASET = 'db_base_dataset';
    const DB_NEW_DATASET = 'db_new_dataset';
    const METADATA_BASE = 'metadata_base';
    const METADATA_NEW = 'metadata_new';

    public function getYamlFilename($name)
    {
        return __DIR__ . '/metadata/' . $name . '.yaml';
    }

    public function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            $this->getYamlFilename(self::DB_BASE_DATASET)
        );
    }

    public function setUp()
    {
        parent::setUp();

        $config = file_get_contents(__DIR__ . '/metadata/fake_sugar/config.tpl.php');
        $config = str_replace(
            array(
                '<DB_USER>',
                '<DB_PASSWORD>',
                '<DB_NAME>'
            ),
            array(
                getenv('SUGARCLI_DB_USER'),
                getenv('SUGARCLI_DB_PASSWORD'),
                getenv('SUGARCLI_DB_NAME'),
            ),
            $config
        );
        file_put_contents(__DIR__ . '/metadata/fake_sugar/config.php', $config);

        $this->app = new Application();
        $this->app->configure();
        $this->app->getContainer()->set('logger', new TestLogger());
        $this->app->registerAllCommands();
    }

    public function tearDown()
    {
        parent::tearDown();
        unlink(__DIR__ . '/metadata/fake_sugar/config.php');
        $this->app = null;
    }
}
