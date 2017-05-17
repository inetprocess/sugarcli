<?php
/**
 * SugarCLI
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Utils;

class TempFile extends \SplFileInfo
{
    public function __construct($prefix = 'php', $content = null, $dir = null)
    {
        if ($dir === null) {
            $dir = sys_get_temp_dir();
        }
        $filename = tempnam($dir, $prefix);
        if ($content != null) {
            file_put_contents($filename, $content);
        }
        parent::__construct($filename);
    }

    public function __destruct()
    {
        if (file_exists($this->getPathname())) {
            unlink($this->getPathname());
        }
    }
}
