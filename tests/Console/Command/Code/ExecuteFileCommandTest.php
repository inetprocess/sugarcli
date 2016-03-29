<?php

namespace SugarCli\Test\Console\Command\Code;

use SugarCli\Tests\TestsUtil\Util;

/**
 * @group sugarcrm-path
 */
class ExecuteFileCommandTest extends \PHPUnit_Framework_TestCase
{
    public static $cmd_name = 'code:execute:file';

    public function testExecute()
    {
        $tester = Util::getTester(self::$cmd_name)->tester;
        $ret = $tester->execute(array(
            'command' => 'code:execute:file',
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'file' => __DIR__ . '/execute.php',
        ));
        $this->assertEquals(0, $ret);
    }

    public function testInvalidFile()
    {
        $tester = Util::getTester(self::$cmd_name)->tester;
        $ret = $tester->execute(array(
            'command' => self::$cmd_name,
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'file' => 'unknown_file.php',
        ));
        $this->assertEquals(5, $ret);
    }

    public function testSugarException()
    {
        $tester = Util::getTester(self::$cmd_name)->tester;
        $ret = $tester->execute(array(
            'command' => self::$cmd_name,
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'file' => __DIR__ . '/sugar_exception.php',
        ));
        $this->assertEquals(20, $ret);
        $this->assertContains('error from sugar', $tester->getDisplay());
    }
}
