<?php

namespace SugarCli\Tests\TestsUtil;

use SugarCli\Tests\TestsUtil\TestLogger;

class TestLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testLogger()
    {
        $logger = new TestLogger();
        $logger->warning('foo');
        $logger->info('test info');
        $logger->debug('test debug');
        $logger->warning('test warn');

        $expected_log_lines = array(
            "[warning] foo\n",
            "[info] test info\n",
            "[debug] test debug\n",
            "[warning] test warn\n",
        );

        $this->assertEquals(implode('', $expected_log_lines), $logger->getLines('debug'));
        $warn = $expected_log_lines[0] . $expected_log_lines[3];
        $this->assertEquals($warn, $logger->getLines('warning'));
        $this->assertEquals($warn, $logger->getLines('notice'));
        $this->assertEquals('', $logger->getLines('error'));
    }
}
