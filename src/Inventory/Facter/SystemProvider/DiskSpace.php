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

use SugarCli\Inventory\Facter\FacterInterface;
use SugarCli\Utils\Utils;

class DiskSpace implements FacterInterface
{
    public function getFacts()
    {
        $disk_size = disk_total_space(getcwd());
        $disk_free = disk_free_space(getcwd());
        $facts = array();
        $facts['disksize_mb'] = round($disk_size / (1024*1024), 2);
        $facts['diskfree_mb'] = round($disk_free / (1024*1024), 2);
        $facts['disksize'] = Utils::humanize($disk_size);
        $facts['diskfree'] = Utils::humanize($disk_free);

        return $facts;
    }
}
