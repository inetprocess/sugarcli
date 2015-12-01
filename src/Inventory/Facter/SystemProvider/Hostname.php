<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use Symfony\Component\Process\Process;
use SugarCli\Inventory\Facter\FacterInterface;

class Hostname implements FacterInterface
{
    public function getFacts()
    {
        $process = new Process('hostname --fqdn');
        $process->mustRun();
        $fqdn = trim($process->getOutput());

        return array(
            'fqdn' => $fqdn,
            'hostname' => gethostname()
        );
    }
}
