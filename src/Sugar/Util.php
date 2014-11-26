<?php

namespace SugarCli\Sugar;

class Util 
{
    public static function is_extracted($path)
    {
        return is_file($path . '/sugar_version.php');
    }

    public static function is_installed($path)
    {
        if(static::is_extracted($path) and is_file($path . '/config.php')) {
            require_once($path . '/config.php');
            if(array_key_exists('installer_locked', $sugar_config)) {
                return $sugar_config['installer_locked'];
            }
        }
        return false;
    }
}
