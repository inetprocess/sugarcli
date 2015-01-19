<?php

namespace SugarCli\Sugar;

class Sugar
{
    protected $path = null;

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

    public function getSugarConfig()
    {
        $path = $this->path;
        if ($this->isExtracted($path) and is_file($path . '/config.php')) {
            require($path . '/config.php');
            if (isset($sugar_config) and is_array($sugar_config)) {
                return $sugar_config;
            }
            throw new SugarException("Invalid sugarcrm configuration file at $path/config.php");
        }
        throw new SugarException("$path is not a valid sugar installation.");
    }
}

