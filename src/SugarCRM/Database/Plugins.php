<?php
/**
 * SugarCRM Tools
 *
 * PHP Version 5.3 -> 5.6
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author iNetProcess
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\SugarCRM\Database;

use Inet\SugarCRM\Database\AbstractTablesDiff;
use Symfony\Component\Yaml\Yaml;

/**
 * Manage upgrade_history table.
 */
class Plugins extends AbstractTablesDiff
{
    protected $tableName = 'upgrade_history';

    /**
     * Fetch plugins array from the sugar database.
     */
    public function loadFromDb()
    {
        $this->getLogger()->debug("Reading {$this->tableName} from DB.");
        $query = $this->getQueryFactory()->createSelectAllQuery($this->tableName);
        $res = $query->execute();
        $plugins = array();
        foreach ($res->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $plugins[$row['id']] = $row;
        }
        ksort($plugins);
        return $plugins;
    }

    /**
     * Fetch plugins array from the definition file
     */
    public function loadFromFile()
    {
        $this->getLogger()->debug('Reading plugins from ' . $this->defFile);
        $plugins = Yaml::parse($this->defFile);
        if (!is_array($plugins)) {
            $plugins = array();
            $this->getLogger()->warning('No definition found in plugins file.');
        }
        $res = array();
        foreach ($plugins as $plugin_data) {
            $res[$plugin_data['id']] = $plugin_data;
        }
        ksort($res);
        return $res;
    }
}
