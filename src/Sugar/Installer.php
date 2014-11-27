<?php

namespace SugarCli\Sugar;

use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Util;

class Installer
{
    public $path;
    public $url;
    public $source;
    public $config;

    public $logger;

    private $fs;

    public function __construct($path = null, $url = null, $source = null, $config = null, $logger = null)
    {
        $this->path = $path;
        $this->url = $url;
        $this->source = $source;
        $this->config = $config;

        $this->logger = $logger;

        $this->fs = new Filesystem();
    }

    public function getConfigTarget()
    {
        return $this->path . '/config_si.php';
    }

    /**
     * Check if install path is empty.
     * @return true if path is empty.
     */
    public function isPathEmpty()
    {
        return Util::isPathEmpty($this->path);
    }

    public function deletePath()
    {
        $this->logger->info("Removing installation path {$this->path}...");
        $this->fs->remove($this->path);
        $this->logger->info("Path {$this->path} was successfully removed.");
    }

    public function createPath()
    {
        $this->logger->info("Creating installation path {$this->path}.");
        $this->fs->mkdir($this->path, 0750);
    }

    public static function junkParent($path) {
        return preg_replace('/^\/?[^\/]+\/(.*)$/', '$1', $path);
    }

    /**
     * Extract the source archive into $this->path.
     * While extracting, we remove the top folder from the filename inside the archive.
     * For example, a file 'SugarPro-Full-7.2.1/soap.php' will get extracted to 
     * <install_path>/soap.php .
     */
    public function extract()
    {
        $this->logger->info("Extracting {$this->source} into {$this->path}...");
        if (!is_dir($this->path) and !$this->isPathEmpty()) {
            throw new InstallerException(
                "The target path {$this->path} is not a directory or is not empty when extracting the archive."
            );
        }
        if (!is_file($this->source)) {
            throw new InstallerException("{$this->source} doesn't exists or is not a file.");
        }

        $zip = new \ZipArchive();
        if (($res = $zip->open($this->source)) !== true) {
            throw new InstallerException("Unable to open zip {$this->source}.");
        }
        $zip_paths = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $zip_paths[$i] = $zip->getNameIndex($i);
        }
        $target_paths = Installer::junkParent($zip_paths);
        foreach ($target_paths as $i => $name) {
            if (empty($name)) continue;

            $target_path = $this->path . '/' . $name;
            // Check is name ends with '/' (directory name)
            if (strpos($name, '/', strlen($name) - 1) === FALSE ) {
                // We have a file name
                // We load each zipped file in memory.
                // It is much faster than getting the Stream handle.
                // For Sugar 7 archive we peak at 24MB so it shouldn't be an issue.
                $content = $zip->getFromIndex($i);
                if ($content === false) {
                    throw new InstallerException("Error while extracting {$name} from the archive.");
                }
                if (file_put_contents($target_path, $content) === false) {
                    throw new InstallerException("Error while writting to file {$target_path}.");
                }
            } else {
               // We have a dir name
               $this->fs->mkdir($target_path);
            }
        }
        
        if (!$zip->close()) {
            throw new InstallerException("Unable to close zip {$this->source}.");
        }
        $this->logger->info("Extraction OK.");

    }

    public function copyConfigSi()
    {
        $this->logger->info("Copying configuration file {$this->config} to ".$this->getConfigTarget().'.');
        $this->fs->copy($this->config, $this->getConfigTarget(), true);
    }

    public function deleteConfigSi()
    {
        $this->logger->info("Deleting configuration file ".$this->getConfigTarget().'.');
        $this->fs->remove($this->getConfigTarget());
    }

    public function callUrl()
    {
    }

    /**
     * Run the complete installation process.
     * @param force If true then remove install directory first.
     */
    public function run($force = false)
    {
        $this->logger->notice("Installing SugarCRM into {$this->path}...");
        if ($this->fs->exists($this->path)) {
            if (!$this->isPathEmpty()) {
                if ($force === true) {
                    $this->deletePath();
                    $this->createPath();
                } else {
                    throw new InstallerException(
                        "The target path {$this->path} is not empty. "
                       ."Use --force to remove {$this->path} and its contents before installing."
                    );
                }
            }
        } else {
            $this->createPath();
        }
        // At this point we should have an empty dir for path.
        $this->extract();
        $this->copyConfigSi();
        $this->callUrl();
        $this->deleteConfigSi();
        $this->logger->notice("Installation complete.");

    }
}
