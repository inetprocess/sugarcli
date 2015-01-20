<?php

namespace SugarCli\Sugar;

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
}

