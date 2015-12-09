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

class InstanceId extends AbstractSugarProvider
{
    public function getFacts()
    {
        $user_passwd = posix_getpwuid(posix_getuid());

        return array(
            'instance_id' => $user_passwd['name'] . '@' . gethostname()
        );
    }
}
