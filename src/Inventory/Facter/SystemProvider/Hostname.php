<?php
/**
 * Inventory
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/inventory
 *
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

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
