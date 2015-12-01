<?php

namespace SugarCli\Inventory\Facter;

use PDO;
use Inet\SugarCRM\Application;

class SugarFacter extends ProviderFacter
{
    protected $sugar;
    protected $pdo;

    public function __construct(Application $sugar, PDO $pdo)
    {
        $this->sugar = $sugar;
        $this->pdo = $pdo;
        $providers_dir = __DIR__ . '/SugarProvider';
        $providers_namespace = __NAMESPACE__ . '\SugarProvider';
        parent::__construct($providers_dir, $providers_namespace);
    }

    /**
     * Inject sugar application into providers.
     *
     * @param string $class_name Name of the class to create.
     */
    public function factory($class_name)
    {
        return new $class_name($this->sugar, $this->pdo);
    }
}
