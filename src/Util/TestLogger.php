<?php

namespace SugarCli\Util;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\HelperSet;

class TestLogger extends AbstractLogger implements HelperInterface
{
    protected $helperSet = null;

    /**
     * Sets the helper set associated with this helper.
     *
     * @param HelperSet $helperSet A HelperSet instance
     */

    public function setHelperSet(HelperSet $helperSet = null)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * Gets the helper set associated with this helper.
     *
     * @return HelperSet A HelperSet instance
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    public function getName()
    {
        return 'logger';
    }

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

