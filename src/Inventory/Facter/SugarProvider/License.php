<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class License extends AbstractSugarProvider
{
    public function getFacts()
    {
        $sql = 'SELECT value FROM config WHERE category="license" AND name = ?';
        $configs = array(
            'expire' => 'expire_date',
            'last_validation' => 'last_validation',
            'last_validation_success' => 'last_validation_success',
            'users' => 'users',
            'validation_key_expire' => 'vk_end_date',
        );
        $stmt = $this->getPdo()->prepare($sql);
        $facts = array();
        foreach ($configs as $key => $name) {
            $stmt->bindValue(1, $name);
            $facts[$key] = $this->queryOne($stmt);
            $stmt->closeCursor();
        }
        $facts['users'] = intval($facts['users']);

        return array('license' => $facts);
    }
}
