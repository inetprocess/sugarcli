<?php
/**
 * Class to manage a sugar instance.
 */

namespace SugarCli\Sugar;

use Psr\Log\LoggerInterface;

class Sugar
{
    protected $path = null;
    protected $config = null;
    protected $initialized = false;
    protected $real_cwd = null;

    public $logger = null;

    public function __construct($path = null, LoggerInterface $logger = null)
    {
        $this->path = $path;
        $this->logger = $logger;
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
            $e;
            return false;
        }
        return false;
    }

    public function isInitialized()
    {
        return $this->initialized;
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

    public function getExternalDb()
    {
        $dbconfig = $this->getSugarConfig();
        $dbconfig = $dbconfig['dbconfig'];

        $params = array(
            'dbname' => $dbconfig['db_name'],
            'user' => $dbconfig['db_user_name'],
            'password' => $dbconfig['db_password'],
            'host' => $dbconfig['db_host_name'],
            'port' => $dbconfig['db_port'],
            'driver' => 'pdo_mysql',
        );

        $conn = \Doctrine\DBAL\DriverManager::getConnection(
            $params,
            new \Doctrine\DBAL\Configuration()
        );

        return $conn;
    }

    public function init()
    {
        if (!$this->isInstalled()) {
            throw new SugarException("{$this->path} is not a valid sugar installation.");
        }
        // @codingStandardsIgnoreStart
        $this->logger->notice("Initialiazing sugar in {$this->path}.");
        // Set include path for sugar files.
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->path);

        $this->real_cwd = getcwd();
        $this->logger->debug("Changing dir to {$this->path}.");
        chdir($this->path);


        if (!defined('sugarEntry')) {
            define('sugarEntry', true);
        }

        $GLOBALS['sugar_config'] = $sugar_config = $this->getSugarConfig();
        $sugar_config;

        require_once('include/entryPoint.php');
        //require_once('include/MVC/SugarApplication.php');
        var_dump(array_keys(get_defined_vars()));
        $this->logger->notice('Sugar is initialized');
        // @codingStandardsIgnoreStop
    }
}

