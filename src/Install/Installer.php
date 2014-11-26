<?php

namespace SugarCli\Install;

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

    public function extract()
    {
        $this->logger->info("Extracting {$this->source} to {$this->path}...");
        if(!is_dir($this->path) and !$this->isPathEmpty()) {
            // TODO: Manage error
        }
        if (!is_file($this->source)) {
            // TODO: manage error
        }

        $zip = new \ZipArchive();
        if (($res = $zip->open($this->source)) !== true) {
            // TODO: manage error
        }
        if(!$zip->extractTo($this->path, 'SugarPro-Full-7.2.2.1')) {
            // TODO: manage error
        }
        if(!$zip->close()) {
            // TODO: manage error
        }
        $this->logger->info("Extraction OK.");

    }

    public function copyConfig()
    {
        $this->logger->info("Copying configuration file {$this->config} to ".$this->getConfigTarget().'.');
        $this->fs->copy($this->config, $this->getConfigTarget(), true);
    }

    public function deleteConfig()
    {
        $this->logger->info("Deleting configuration file ".$this->getConfigTarget().'.');
        $this->fs->remove($this->getConfigTarget());
    }

    public function callUrl()
    {
    }

    public function run($force = false)
    {
        $this->logger->notice("Installing SugarCRM into {$this->path}...");
        if($this->fs->exists($this->path)) {
            if(!$this->isPathEmpty()) {
                if($force === true) {
                    $this->deletePath();
                } else {
                    // TODO: manage path not empty and not force
                }
            }
        } else {
            $this->createPath();
        }
        // At this point we should have an empty dir for path.
        $this->extract();
        $this->copyConfig();
        $this->callUrl();
       // $this->deleteConfig();
        $this->logger->notice("Installation complete.");

    }
}
