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
     * Generate a YAML file from an array
     *
     * @param array  $data
     * @param string $outputFile
     *
     * @throws \InvalidArgumentException
     */
    public static function generateYaml(array $data, $outputFile)
    {
        $outputFileDir = dirname($outputFile);
        if (!is_dir($outputFileDir)) {
            throw new \InvalidArgumentException("$outputFileDir is not a valid directory (" . __FUNCTION__ . ')');
        }

        $dumper = new YamlDumper();
        $dumper->setIndentation(4);
        $yaml = $dumper->dump($data, 3);
        file_put_contents($outputFile, $yaml);

        return true;
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
}
