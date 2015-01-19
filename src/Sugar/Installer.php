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

    public static function junkParent($path)
    {
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
        if ($zip->open($this->source) !== true) {
            throw new InstallerException("Unable to open zip {$this->source}.");
        }
        $zip_paths = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $zip_paths[$i] = $zip->getNameIndex($i);
        }
        $target_paths = Installer::junkParent($zip_paths);
        foreach ($target_paths as $i => $name) {
            if (empty($name)) {
                continue;
            }

            $target_path = $this->path . '/' . $name;
            // Check is name ends with '/' (directory name)
            if (strpos($name, '/', strlen($name) - 1) === false ) {
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

    /**
     * Call the url to run the Sugar silent install.
     * @param timeout Default to 5 minutes.
     */
    public function callUrl($timeout = 300)
    {
        $real_url = $this->url . "/install.php?goto=SilentInstall&cli=true";
        $this->logger->notice("Calling {$real_url} to install Sugar.");
        $context = stream_context_create(
            array(
                'http' => array(
                    'timeout' => $timeout
                )
            )
        );
        $h = fopen($real_url, 'r', false, $context);
        if ($h === false) {
            throw new InstallerException("Could not connect to the specified url.");
        }

        $installer_res = '';
        while (!feof($h)) {
            $installer_res .= fread( $h, 1048576 );
        }
        $metadata = stream_get_meta_data($h);
        if (fclose($h) === false) {
            throw new InstallerException("Unable to close the url.");
        }

        if ($metadata['timed_out']) {
            throw new InstallerException(
                "The web installer took longer than {$timeout} to finish. It is probably still running."
            );
        }
        // find the bottle message
        if (preg_match('/<bottle>(.*)<\/bottle>/s', $installer_res, $msg) === 1) {
            $this->logger->info("The web installer was successfully completed.");
            $this->logger->info("Web installer: {$msg[1]}");
        } elseif (preg_match('/Exit (.*)/s', $installer_res, $msg)) {
            $this->logger->info("Web installer: {$msg[1]}");
            throw new InstallerException(
                "The web installer failed. Check your config_si.php file."
            );
        } else {
            $this->logger->debug("Web installer: {$installer_res}");
            throw new InstallerException(
                "The web installer failed and return an unknown error. Check the install.log file on Sugar."
            );
        }
    }

    /**
     * Run the complete installation process.
     * @param force If true then remove install directory first.
     */
    public function run($force = false)
    {
        if (!is_readable($this->config)) {
            throw new InstallerException("Missing or unreadable config_si file {$this->config}.");
        }
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

