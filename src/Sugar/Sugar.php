<?php
/**
 * Class to manage a sugar instance.
 */

namespace SugarCli\Sugar;

class Sugar
{
    protected $path = null;
    protected $config = null;

    public function __construct($path = null)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->clearCache();
        $this->path = $path;
    }

    public function isExtracted()
    {
        return is_file($this->path . '/sugar_version.php');
    }

    public function isInstalled()
    {
        try {
            $sugar_config = $this->getSugarConfig();
            if (array_key_exists('installer_locked', $sugar_config)) {
                return $sugar_config['installer_locked'];
            }
        } catch (SugarException $e) {
            return false;
        }
        return false;
    }

    public function clearCache()
    {
        $this->config = null;
    }

    public function getSugarConfig($clear_cache = false)
    {
        if ($clear_cache) {
            $this->clearCache();
        }
        if ($this->config == null) {
            $path = $this->path;
            if ($this->isExtracted($path) and is_file($path . '/config.php')) {
                require($path . '/config.php');
                if (isset($sugar_config) and is_array($sugar_config)) {
                    $this->config = $sugar_config;
                } else {
                    throw new SugarException("Invalid sugarcrm configuration file at $path/config.php");
                }
            } else {
                throw new SugarException("$path is not a valid sugar installation.");
            }
        }
        return $this->config;
    }
}

