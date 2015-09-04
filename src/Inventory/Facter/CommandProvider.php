<?php

namespace SugarCli\Inventory\Facter;

use Symfony\Component\Process\Process;

class CommandProvider implements FacterInterface
{
    protected $cmd;
    protected $as_json;

    public function __construct($command, $as_json = false)
    {
        $this->cmd = $command;
        $this->as_json = $as_json;
    }

    /**
     * Return the facts generated from the command.
     * If as_json is true, the command must produce valid json.
     */
    public function getFacts()
    {
        try {
            $output = $this->runCommand($this->cmd);
            if ($this->as_json) {
                $json = json_decode($output, true);
                if (is_null($json)) {
                    return array();
                }
                return $json;
            } else {
                return $this->parseFacts($output);
            }
        } catch (\Exception $e) {
            return array();
        }
    }

    /**
     * Run the command.
     * @param $cmd Command line to run
     * @return oupout of command
     */
    protected function runCommand($cmd)
    {
        $process = new Process($cmd);
        $process->mustRun();
        return $process->getOutput();
    }

    /**
     * Parse flat results in the form
     * fact1=value1
     * fact2=value2
     */
    protected function parseFacts($facts_string)
    {
        $facts = array();
        foreach (explode(PHP_EOL, $facts_string) as $line) {
            if (empty($line)) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $facts[$key] = $value;
        }
        return $facts;
    }
}
