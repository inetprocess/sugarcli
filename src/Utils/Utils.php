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

namespace SugarCli\Utils;

/**
 * Various Utils
 */
class Utils
{
    /**
     * List of Prefixes
     *
     * @var array
     */
    public static $siPrefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );

    /**
     * Humanize the size bu add a Prefix
     *
     * @param integer $bytes
     * @param integer $base
     *
     * @return string Readable size
     */
    public static function humanize($bytes, $base = 1024)
    {
        if (empty($bytes)) {
            return '0 B';
        }
        $class = min((int)log($bytes, $base), count(static::$siPrefix) - 1);

        return sprintf('%1.2F %s', $bytes / pow($base, $class), static::$siPrefix[$class]);
    }

    /**
     * Create a new line every X words
     *
     * @param string  $sentence
     * @param integer $cutEvery
     *
     * @return string Same sentence cut
     */
    public static function newLineEveryXWords($sentence, $cutEvery)
    {
        // New line every 5 words
        $words = explode(' ', $sentence);
        for ($i = 0; $i < count($words); $i++) {
            $words[$i] = ($i !== 0 && $i%$cutEvery === 0 ? PHP_EOL : '') . $words[$i];
        }

        return implode(' ', $words);
    }
}
