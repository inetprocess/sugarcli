<?php

namespace SugarCli\Tests\Sugar;

use SugarCli\Sugar\LangFileCleaner;
use SugarCli\Tests\TestsUtil\TestLogger;

class LangFileCleanerTest extends \PHPUnit_Framework_TestCase
{
    public function testCleanEmpty()
    {
        $logger = new TestLogger();
        $cleaner = new LangFileCleaner(__DIR__, $logger);
        $this->assertFalse($cleaner->clean());
        $this->assertEquals('[notice] No lang files found to process.' . PHP_EOL, $logger->getLines('notice'));
    }

    public function testClean()
    {
        $fake_sugar = __DIR__ . '/fake_sugar';
        $logger = new TestLogger();
        $cleaner = new LangFileCleaner($fake_sugar, $logger);
        $this->assertTrue($cleaner->clean());
    }
}
