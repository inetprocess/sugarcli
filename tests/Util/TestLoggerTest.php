<?php

namespace SugarCli\Util;

use SugarCli\Util\TestLogger;

class TestLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider loggerProvider
     */
    public function testLogger($expect, $level, $lines)
    {
        $logger = new TestLogger();
        foreach ($lines as $line) {
            $logger->log($line[0], $line[1]);
        }
        $this->assertEquals("$expect\n", $logger->getLines($level));
    }

    public function loggerProvider()
    {
        $json = <<<EOF
    [
        [ "[warning] foo", "warning", [ [ "warning", "foo", [] ]] ],
        [ "[debug] bar", "debug", [
                [ "warning", "foo"],
                [ "debug", "bar"],
                [ "warning", "warn"]
            ]
        ]
    ]
EOF;
        $test_data = json_decode($json);
        return $test_data;
    }
}

