<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class SpaceUsage extends AbstractSugarProvider
{
    protected function getDiskSpaceUsage()
    {
        $du_output = rtrim($this->exec('/usr/bin/du -B1 -s .', $this->getPath()));
        $matches = array();
        if (preg_match('/(\d+)\s+\.$/', $du_output, $matches) === 1) {
            return $matches[1];
        }
        return null;
    }

    protected function getDBSpaceUsage()
    {
        $sql = 'SELECT ROUND(SUM( data_length + index_length), 2) As size';
        $sql .= ' FROM information_schema.TABLES';
        $sql .= ' WHERE table_schema = ?';
        $sql .= ' GROUP BY table_schema';
        $stmt = $this->getPdo()->prepare($sql);
        $sugar_config = $this->getApplication()->getSugarConfig();
        $stmt->bindValue(1, $sugar_config['dbconfig']['db_name']);
        return $this->queryOne($stmt);
    }

    public function humanize($bytes)
    {
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes, $base), count($si_prefix) - 1);
        return sprintf('%1.2f', $bytes / pow($base, $class)) . ' ' . $si_prefix[$class];
    }

    public function getFacts()
    {
        $disk_used = $this->getDiskSpaceUsage();
        $db_used = $this->getDBSpaceUsage();
        $facts = array(
            'disk_used_mb' => round($disk_used / (1024*1024), 2),
            'disk_used' => $this->humanize($disk_used),
            'db_used_mb' => round($db_used / (1024*1024), 2),
            'db_used' => $this->humanize($db_used),
        );
        return $facts;
    }
}
