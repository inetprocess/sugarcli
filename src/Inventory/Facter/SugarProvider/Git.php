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

use Symfony\Component\Process\Exception\ProcessFailedException;
use SugarCli\Inventory\Facter\AbstractSugarProvider;

class Git extends AbstractSugarProvider
{
    public function isGit()
    {
        try {
            $this->exec('git rev-parse --git-dir', $this->getPath());
        } catch (ProcessFailedException $e) {
            return false;
        }

        return true;
    }

    protected function execOrNull($cmd)
    {
        try {
            return rtrim($this->exec($cmd, $this->getPath()));
        } catch (ProcessFailedException $e) {
        }

        return null;
    }

    public function getModifiedFiles()
    {
        try {
            return substr_count($this->exec('git status --porcelain', $this->getPath()), "\n");
        } catch (ProcessFailedException $e) {
        }

        return null;
    }

    public function getFacts()
    {
        // Modified files
        // git status --porcelain

        if (!$this->isGit()) {
            return array();
        }
        $facts = array('git' => array());
        $facts['git']['tag'] = $this->execOrNull('git describe --tags --always HEAD');
        $facts['git']['branch'] = $this->execOrNull('git rev-parse --abbrev-ref HEAD');
        $facts['git']['origin'] = $this->execOrNull('git config --get remote.origin.url');
        $facts['git']['modified_files'] = $this->getModifiedFiles();

        return $facts;
    }
}
