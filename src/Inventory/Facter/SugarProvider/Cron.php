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

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class Cron extends AbstractSugarProvider
{
    protected function isCronInstalled()
    {
        $crontabs = $this->exec('crontab -l');
        $path = rtrim($this->getPath(), '/');
        $re = '@^(\*\s+){5}.*' . $path . '.*cron.php.*$@m';

        return preg_match($re, $crontabs) === 1;
    }

    public function getFacts()
    {
        $queries = array(
            'last_run' => 'SELECT MAX(`last_run`) AS last_run FROM `schedulers`',
        );
        $facts = array();
        foreach ($queries as $key => $sql) {
            $stmt = $this->getPdo()->prepare($sql);
            $facts[$key] = $this->queryOne($stmt);
        }
        $facts['installed'] = $this->isCronInstalled();

        return array('cron' => $facts);
    }
}
