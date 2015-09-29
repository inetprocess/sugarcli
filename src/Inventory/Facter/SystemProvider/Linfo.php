<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use Linfo\Linfo as Info;
use SugarCli\Inventory\Facter\FacterInterface;
use SugarCli\Utils\Utils;

class Linfo implements FacterInterface
{
    public function getFacts()
    {
        $old_error_reporting = error_reporting(0);
        $linfo = new Info();
        $parser = $linfo->getParser();

        $cpu_info = $parser->getCpu();
        $uptime = $parser->getUptime();
        $uptime_s = time() - $uptime['bootedTimestamp'];

        $distro = $parser->getDistro();

        $ram = $parser->getRam();
        error_reporting($old_error_reporting);
        return array(
            'fqdn' => $parser->getHostName(),
            'processors' => array(
                'models' => array_column($cpu_info, 'Model'),
                'count' => count($cpu_info),
            ),
            'system_uptime' => array(
                'seconds' => $uptime_s,
                'hours' => floor($uptime_s / 3600),
                'days' => floor($uptime_s / (3600 * 24)),
                'uptime' => $uptime['text'],
            ),
            'architecture' => $parser->getCPUArchitecture(),
            'os' => array(
                'name' => $distro['name'],
                'lsb' => array(
                    'distdescription' => $distro['name'] . ' ' . $parser->getOS() . ' ' . $distro['version'],
                ),
            ),
            'memoryfree_mb' => round($ram['free'] / (1024 * 1024), 2),
            'memorysize_mb' => round($ram['total'] / (1024 * 1024), 2),
            'swapfree_mb' => round($ram['swapFree'] / (1024*1024), 2),
            'swapsize_mb' => round($ram['swapTotal'] / (1024*1024), 2),
            'memoryfree' => Utils::humanize($ram['free']),
            'memorysize' => Utils::humanize($ram['total']),
            'swapfree' => Utils::humanize($ram['swapFree']),
            'swapsize' => Utils::humanize($ram['swapTotal']),
            // RAW data from linfo.
            /* 'linfo' => array( */
            /*     'processors' => $parser->getCpu(), */
            /*     'kernel' => $parser->getKernel(), */
            /*     'hostname' => $parser->getHostName(), */
            /*     'os' => $parser->getOS(), */
            /*     'arch' => $parser->getCPUArchitecture(), */
            /*     'ram' => $parser->getRam(), */
            /*     'uptime' => $parser->getUptime(), */
            /*     'mounts' => $parser->getMounts(), */
            /*     'load' => $parser->getLoad(), */
            /*     'net' => $parser->getNet(), */
            /*     'procs' => $parser->getProcessStats(), */
            /*     'distro' => $parser->getDistro(), */
            /*     'users' => $parser->getNumLoggedIn(), */
            /*     'virt' => $parser->getVirtualization(), */
            /*     'model' => $parser->getModel(), */
            /* ) */
        );
    }
}
