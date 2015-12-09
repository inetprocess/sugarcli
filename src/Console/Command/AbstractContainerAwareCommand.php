<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.6
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcli
 *
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Command\Command;

abstract class AbstractContainerAwareCommand extends Command
{
    public function getContainer()
    {
        return $this->getApplication()->getContainer();
    }

    public function getService($service)
    {
        return $this->getContainer()->get($service);
    }

    public function setSugarPath($path)
    {
        $this->getContainer()->setParameter('sugarcrm.path', $path);
        $this->getContainer()->compile();
    }
}
