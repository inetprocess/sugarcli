<?php

namespace SugarCli\Inventory;

use Guzzle\Service\Client as GClient;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Http\Exception\ClientErrorResponseException;

use Psr\Log\LoggerInterface;

use SugarCli\Inventory\Facter\FacterInterface;

/**
 * Send gathered facts to Inventory server.
 */
class Agent
{
    protected $facters;
    protected $logger;
    protected $client;

    const SYSTEM = 0;
    const SUGARCRM = 1;

    public function __construct(LoggerInterface $logger, GClient $client)
    {
        $this->facters = array();
        $this->logger = $logger;
        $this->client = $client;

        $this->client->setDescription(
            ServiceDescription::factory(__DIR__ . '/InventoryService.json')
        );
    }

    public function setFacter(FacterInterface $facter, $type)
    {
        $this->facters[$type] = $facter;
    }

    public function getFacter($type)
    {
        if (!isset($this->facters[$type])) {
            throw new \RuntimeException('No facter found for this type. Please set the facter object first.');
        }
        return $this->facters[$type];
    }

    public function getFacts($type)
    {
        return $this->getFacter($type)->getFacts();
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function sendServer()
    {
        $this->getLogger()->info('Fetching system facts.');
        $facts = $this->getFacts(self::SYSTEM);
        $fqdn = $facts['hostname'];
        $client = $this->getClient();
        $server_data = array(
            'fqdn' => $fqdn,
            'facts' => $facts,
        );
        try {
            $this->getLogger()->info('Try to put data to existing server record.');
            $server_data['fqdn_uri'] = $fqdn;
            $client->putServer($server_data);
        } catch (ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                // The server doesn't exist yet. We need to POST it.
                $this->getLogger()->info('Server was not found on PUT request. Doing POST to create it.');
                $client->postServer($server_data);
            } else {
                // This is not a 404 error, throw the exception.
                throw $e;
            }
        }
        $this->getLogger()->info('The server information has been successfully sent.');
    }
}
