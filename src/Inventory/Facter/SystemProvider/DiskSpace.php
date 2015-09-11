<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use SugarCli\Inventory\Facter\FacterInterface;

class DiskSpace implements FacterInterface
{
    public function humanize($bytes)
    {
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes, $base), count($si_prefix) - 1);
        return sprintf('%1.2f', $bytes / pow($base, $class)) . ' ' . $si_prefix[$class];
    }

    public function getFacts()
    {
        $disk_size = disk_total_space(getcwd());
        $disk_free = disk_free_space(getcwd());
        $facts = array();
        $facts['disksize_mb'] = round($disk_size / (1024*1024), 2);
        $facts['diskfree_mb'] = round($disk_free / (1024*1024), 2);
        $facts['disksize'] = $this->humanize($disk_size);
        $facts['diskfree'] = $this->humanize($disk_free);
        return $facts;
    }
}
