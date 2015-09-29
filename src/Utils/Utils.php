<?php

namespace SugarCli\Utils;

class Utils
{
    public static $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );

    public static function humanize($bytes, $base = 1024)
    {
        $class = min((int)log($bytes, $base), count(static::$si_prefix) - 1);
        return sprintf('%1.2f', $bytes / pow($base, $class)) . ' ' . static::$si_prefix[$class];
    }
}
