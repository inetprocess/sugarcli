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
            ->in($this->path)
            ->path('/^custom\/include\/language/')
            ->depth('== 3')
            ->name('*.lang.php');
        $found_one = false;
        foreach ($finder as $file) {
            $this->logger->notice('Processing file ' . $file);
            $found_one = true;
            $content = file_get_contents($file);
            if ($content === false) {
                throw new \Exception('Unable to load the file contents of ' . $file . '.');
            }
            $lang = new LangFile($content, $test, $this->logger);
            file_put_contents($file, $lang->getSortedFile($sort));
        }
        if (!$found_one) {
            $this->logger->notice('No lang files found to process.');
            return false;
        }
        return true;
    }
}
