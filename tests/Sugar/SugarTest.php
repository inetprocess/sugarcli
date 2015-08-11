<?php

namespace SugarCli\Tests\Sugar;

use SugarCli\Sugar\Sugar;

class SugarTest extends \PHPUnit_Framework_TestCase
{
    public function testSugarPath()
    {
        $sugar = new Sugar(__DIR__ . '/fake_sugar');
        $this->assertTrue($sugar->isExtracted());
        $this->assertTrue($sugar->isInstalled());
        $conf = $sugar->getSugarConfig();
        $this->assertEquals('localhost', $conf['dbconfig']['db_host_name']);

        $sugar->setPath(__DIR__);
        $this->assertFalse($sugar->isExtracted());
        $this->assertFalse($sugar->isInstalled());
    }

    /**
     * @expectedException \SugarCli\Sugar\SugarException
     */
    public function testFailSugarPath()
    {
        $sugar = new Sugar(__DIR__);
        $sugar->getSugarConfig();
    }


    public function testSugarConfig()
    {
        $sugar = new Sugar(__DIR__ . '/fake_sugar');
        $actual_config = $sugar->getSugarConfig();
        require(__DIR__ . '/fake_sugar/config.php');
        $sugar_config;
        $this->assertEquals($sugar_config, $actual_config);
    }

    /**
     * @expectedException \SugarCli\Sugar\SugarException
     */
    public function testInvalidSugarConfig()
    {
        $sugar = new Sugar(__DIR__ . '/invalid_sugar');
        $sugar->getSugarConfig();
    }

    /**
     * @expectedException \SugarCli\Sugar\SugarException
     */
    public function testMissingDbConfig()
    {
        $stub = $this->getMockBuilder('\SugarCli\Sugar\Sugar')
            ->setConstructorArgs(array('sugar_path'))
            ->setMethods(array('getSugarConfig'))
            ->getMock();

        $stub->method('getSugarConfig')
            ->willReturn(array());

        $stub->getExternalDb();
    }

    public function testDbParamsNormalization()
    {
        $db_data = array(
            'db_name' => 'test_db',
            'db_user_name' => 'test_user',
        );
        $sugar = new Sugar();
        $actual = $sugar->normalizeDbParams($db_data);
        $expected['db_name'] = 'test_db';
        $expected['db_user_name'] = 'test_user';
        $expected['db_password'] = '';
        $expected['db_host_name'] = 'localhost';
        $expected['db_port'] = 3306;
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \SugarCli\Sugar\SugarException
     */
    public function testDbMissingDbName()
    {
        $db_data = array(
            'db_user_name' => 'test_user',
        );
        $sugar = new Sugar();
        $sugar->normalizeDbParams($db_data);
    }

    /**
     * @expectedException \SugarCli\Sugar\SugarException
     */
    public function testDbMissingDbUserName()
    {
        $db_data = array(
            'db_name' => 'test_db',
        );
        $sugar = new Sugar();
        $sugar->normalizeDbParams($db_data);
    }
}
