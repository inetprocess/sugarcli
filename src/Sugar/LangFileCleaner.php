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


    /**
     * Clean all sugar language files.
     */
    public function clean($sort = true, $test = false)
    {
        $finder = new Finder();
        $finder->files()
            ->depth('== 0')
            ->name('*.lang.php');

        // Add only if found real directories in the following paths.
        $search_paths = array(
            'custom/include/language',
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
        if (!$found_one) {
            $this->logger->notice('No lang files found to process.');
        } else {
            foreach($finder as $file) {
                $this->logger->notice('Processing file ' . $file);
                $lang = new LangFile($file, $test, $this->logger);
                file_put_contents($file, $lang->getSortedFile($sort));
            }
        }
    }
}
