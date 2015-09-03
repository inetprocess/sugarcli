<?php

namespace SugarCli\Tests\TestsUtil;

use Psr\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    public $logLevels = array(
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7,
    );

    public $lines = array();

    public function log($level, $message, array $context = array())
    {
        $this->lines[] = array(
            $level,
            $message,
            $context
        );
    }

    public function getLevelInt($level)
    {
        return $this->logLevels[$level];
    }

    public function isLevelEqualOrLowerThan($test, $reference)
    {
        $reference = $this->getLevelInt($reference);
        $test = $this->getLevelInt($test);
        return ($test <= $reference);
    }

    public function getLines($level = 'warning')
    {
        $lines = '';
        foreach ($this->lines as $line) {
            if ($level === null or $this->isLevelEqualOrLowerThan($line[0], $level)) {
                $lines .= "[${line[0]}] ${line[1]}\n";
            }
        }
        return $lines;
    }

    public function clear()
    {
        $this->lines = array();
    }
}
