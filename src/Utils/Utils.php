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
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Utils;

use Symfony\Component\Yaml\Dumper as YamlDumper;
use Webmozart\PathUtil\Path;
use Inet\SugarCRM\Application as SugarApp;

/**
 * Various Utils
 */
class Utils
{
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
        $numWords = count($words);
        for ($i = 0; $i < $numWords; $i++) {
            $words[$i] = ($i !== 0 && $i%$cutEvery === 0 ? PHP_EOL : '') . $words[$i];
        }

        return implode(' ', $words);
    }

    /**
     * Concat two path member only if the second is not absolute
     * and make the result relative to the last parameter.
     */
    public static function makeConfigPathRelative($config_path, $option_path, $current_path = null)
    {
        $current_path = ($current_path === null)  ? getcwd() : $current_path;
        $config_path = Path::makeAbsolute($config_path, $current_path);
        $absolute_path = Path::makeAbsolute($option_path, $config_path);
        $relative_path = Path::makeRelative($absolute_path, $current_path);
        return ($relative_path === '') ? '.' : $relative_path;
    }


    /**
     * Create a temprorary file with database credentials from sugarcrm application.
     * For use with mysql* commands parameter --defaults-file
     * @return SplFileInfo Temp file is deleted when the object is deleted.
     */
    public static function createTempMySQLDefaultFileFromSugarConfig(SugarApp $sugar_app)
    {
        $sugar_config = $sugar_app->getSugarConfig();
        $dbconfig = $sugar_config['dbconfig'];
        if ($dbconfig['db_type'] != 'mysql') {
            throw new \InvalidArgumentException("Database of type '{$dbconfig['db_type']}' is not supported");
        }
        $conf[] = "[mysql]";
        $params = array(
            'db_user_name' => 'user',
            'db_password' => 'password',
            'db_host_name' => 'host',
            'db_port' => 'port',
        );
        foreach ($params as $sugar_param => $mysql_param) {
            if (!empty($dbconfig[$sugar_param])) {
                $conf[] = implode("=", array($mysql_param, $dbconfig[$sugar_param]));
            }
        }
        return new TempFile('sugarcli_mysql_defaults.cnf.', implode("\n", $conf));
    }
}
