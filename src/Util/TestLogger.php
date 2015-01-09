<?php

namespace SugarCli\Util;

use Psr\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    public $lines = array();

    public function log($level, $message, array $context = array())
    {
        $this->lines[] = array(
            $level,
            $message,
            $context
        );
    }

    public function getLines($level = 'warning')
    {
        $lines = '';
        foreach ($this->lines as $line) {
            if ($level == $line[0]) {
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

