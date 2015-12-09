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

class Config extends AbstractSugarProvider
{
    public function getFacts()
    {
        $sugar_config = $this->getApplication()->getSugarConfig();
        $facts = array(
            'url' => $sugar_config['site_url'],
            'unique_key' => $sugar_config['unique_key'],
            'log_level' => $sugar_config['logger']['level'],
        );

        return $facts;
    }
}
