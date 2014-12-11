<?php
/**
 * Sort arrays inside sugarcrm lang files.
 */

namespace SugarCli\Sugar;

use Symfony\Component\Finder\Finder;

class LangFileCleaner
{

    public $path;

    public $logger;

    public function __construct($path = null, $logger = null)
    {
        $this->path = $path;
        $this->logger = $logger;
    }

    public static function arrayToString($a)
    {
        return var_export($a, true);
    }

    /**
     * Export a variable as a string.
     * @param name Full name of the variable.
     * @param data Variable value to be exported.
     * @param space Surround the equal sign with spaces.
     * @return A string that can be written to a file.
     */
    public static function varToFile($name, $data, $space = true)
    {
        $ret = $name;
        if ($space) {
            $ret .= ' = ';
        } else {
            $ret .= '=';
        }
        return $ret . var_export($data, true) . ";\n";

    }

    /**
     * Export array of data as a string to be written to a file.
     * @param prefix Prefix for the array name.
     * @param suffix Suffix for the array name.
     * @param data Value of the array.
     * @param space Add spaces around equal sign.
     * @param sort Sort the variables by name.
     * @return A string.
     */
    public function getSortedArray($prefix, $suffix, $data, $space = true, $sort = true)
    {
        $ret = '';
        if ($sort) {
            ksort($data);
        }
        foreach ($data as $key => $lists) {
            $var_name = $prefix . $key . $suffix;
            if (! is_array($lists) ) {
                $this->logger->warning("$var_name is not an array. Writing as is.").
                $ret .= static::varToFile($var_name, $lists, $space);
                continue;
            }
            if ($sort) {
                ksort($lists);
            }
            foreach ($lists as $lname => $list) {
                $full_name = "${var_name}['${lname}']";
                $ret .= static::varToFile($full_name, $list, $space);
            }
        }
        return $ret;
    }

    /**
     * Load variables from a file and return a sorted cleaned version.
     * @param lang_file Filename of the file to load.
     * @param sort If true it will sort the variables.
     * @return A string with the original file data sorted.
     */
    public function getSortedFile($lang_file, $sort = true)
    {
        // Pre-define variables
        $existing_vars = array();
        $existing_globals = array();
        $new_vars = array();
        $new_globals = array();
        // Trick sugar with the entry point.
        if (!defined('sugarEntry')) define('sugarEntry', true);


        $existing_vars = get_defined_vars();
        $this->logger->debug(static::arrayToString(array_keys($existing_vars)));
        $existing_globals = array_flip(array_keys($GLOBALS));
        $this->logger->debug(static::arrayToString(array_keys($existing_globals)));

        //Load the lang file to fill variables.
        require($lang_file);


        $this->logger->info("Variables found in file:");
        $new_vars = array_diff_key(get_defined_vars(), $existing_vars);
        $this->logger->info(static::arrayToString(array_keys($new_vars)));

        $new_globals = array_diff_key($GLOBALS, $existing_globals);
        $this->logger->info(static::arrayToString(array_keys($new_globals)));

        // Now we have all our variables.
        // Let's generate the new file.
        $ret_file = "<?php\n";
        $ret_file .= $this->getSortedArray('$', '', $new_vars, true, $sort);
        $ret_file .= $this->getSortedArray('$GLOBALS[\'', '\']', $new_globals, false, $sort);


        //Unset GLOBALS to clean env.
        foreach (array_keys($new_globals) as $key) {
            unset($GLOBALS[$key]);
        }
        return $ret_file;
    }


    /**
     * Clean all sugar language files.
     */
    public function clean()
    {
        $finder = new Finder();
        $finder->files()
            ->depth('== 0')
            ->name('*.lang.php');

        // Add only if found real directories in the following paths.
        $search_paths = array(
            'custom/include/language',
            // Do not mange modules languages for now. 
            // We have issues with sugar 6.2.1
            //'custom/modules/*/language',
        );
        $found_one = false;
        foreach ($search_paths as $sp) {
            $real_path = $this->path . '/' . $sp;
            $found_globs = glob($real_path);
            if (!empty($found_globs)) {
                foreach ($found_globs as $glob) {
                    if (is_dir($glob)) {
                        $finder->in($real_path);
                        $found_one = true;
                        break;
                    }
                }
            }
        }
        if (! $found_one) {
            $this->logger->notice('No lang files found to process.');
        } else {
            foreach($finder as $file) {
                $this->logger->notice('Processing file ' . $file);
                file_put_contents($file, $this->getSortedFile($file));
            }
        }
    }
}
