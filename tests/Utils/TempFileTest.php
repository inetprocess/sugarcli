<?php

namespace SugarCli\Tests\Utils;

use SugarCli\Utils\TempFile;

class TestFileTest extends \PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $tmp = new TempFile();
        $filename = $tmp->getPathname();
        $this->assertFileExists($filename);
        $this->assertStringStartsWith(sys_get_temp_dir() . '/php', $filename);
        unset($tmp);
        $this->assertFileNotExists($filename);
    }

    public function testAllOptions()
    {
        $tmp = new TempFile('phpunit', 'foo bar', __DIR__);
        $filename = $tmp->getPathname();
        $this->assertFileExists($filename);
        $this->assertStringStartsWith(__DIR__ . '/php', $filename);
        $this->assertStringEqualsFile($filename, 'foo bar');
        unset($tmp);
        $this->assertFileNotExists($filename);
    }
}
