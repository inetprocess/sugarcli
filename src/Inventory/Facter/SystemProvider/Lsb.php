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

class Lsb implements FacterInterface
{
    public function parseOutput($output)
    {
        $lsb = array();
        foreach (explode("\n", trim($output)) as $line) {
            list($key, $value) = explode("\t", $line, 2);
            $lsb[trim($key, ':')] = $value;
        }

        return $lsb;
    }

    public function getFacts()
    {
        $process = new Process('lsb_release -cidr');
        $process->mustRun();
        $lsb = $this->parseOutput($process->getOutput());
        $version = explode('.', $lsb['Release'], 2);
        $major = $version[0];
        $minor = @$version[1];

        return array(
            'os' => array(
                'name' => $lsb['Distributor ID'],
                'release' => array(
                    'major' => $major,
                    'minor' => $minor,
                    'full' => $lsb['Release'],
                ),
                'lsb' => array(
                    'distcodename' => $lsb['Codename'],
                    'distid' => $lsb['Distributor ID'],
                    'distdescription' => $lsb['Description'],
                    'distrelease' => $lsb['Release'],
                    'majdistrelease' => $major,
                    'minordistrelease' => $minor,
                ),
            ),
        );
    }
}
