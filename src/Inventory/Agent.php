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
    protected $account_name;

    const SYSTEM = 0;
    const SUGARCRM = 1;

    public function __construct(LoggerInterface $logger, GClient $client, $account_name = '')
    {
        $this->facters = array();
        $this->logger = $logger;
        $this->account_name = $account_name;

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
        $server_data = array(
            'fqdn' => $fqdn,
            'facts' => $facts,
        );
        $this->sendEntity('Server', $server_data, 'fqdn');
    }

    public function sendAccount()
    {
        $this->sendEntity(
            'Account',
            array('name' => $this->account_name),
            'name'
        );
    }

    public function getAccountId($account_name)
    {
        return $this->getEntityId('Account', 'name', $account_name);
    }

    public function getServerId($server_fqdn)
    {
        return $this->getEntityId('Server', 'fqdn', $server_fqdn);
    }

    public function getEntityId($entity_name, $key_id, $search)
    {
        $client = $this->getClient();
        try {
            $cmd = $client->getCommand('get' . $entity_name, array($key_id => $search));
            $entity = $cmd->execute();
            $id = $entity->get('id');
        } catch (ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                $id = null;
            } else {
                throw $e;
            }
        }
        return $id;
    }

    public function sendSugarInstance($server_id = null, $account_id = null)
    {
        $this->getLogger()->info('Fetch sugarcrm facts.');
        $facts = $this->getFacts(self::SUGARCRM);
        $sugar_data = array(
            'url' => $facts['instance_id'],
            'facts' => $facts,
        );
        if (!is_null($server_id)) {
            $sugar_data['server'] = $server_id;
        }
        if (!is_null($account_id)) {
            $sugar_data['account'] = $account_id;
        }
        $this->sendEntity('SugarInstance', $sugar_data, 'url');
    }

    /**
     * Send an entity.
     * Try put first if 404 create it with POST.
     * @param string $entity_name Entity name in CamelCase
     * @param array $data Entity data to send.
     * @param string $key_id Key of data to use as an id for the request.
     */
    public function sendEntity($entity_name, array $data, $key_id)
    {
        $client = $this->getClient();
        $this->getLogger()->info('Sending new ' . $entity_name . '.');
        try {
            $this->getLogger()->info('Try to PUT data to existing ' . $entity_name . ' record.');
            $data[$key_id . '_uri'] = $data[$key_id];
            $client->getCommand('put' . $entity_name, $data)
                ->execute();
        } catch (ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                // The server doesn't exist yet. We need to POST it.
                $this->getLogger()->info($entity_name . ' was not found on PUT request. Doing POST to create it.');
                $client->getCommand('post' . $entity_name, $data)
                    ->execute();
            } else {
                // This is not a 404 error, throw the exception.
                throw $e;
            }
        }
        $this->getLogger()->info('The ' . $entity_name . ' information has been successfully sent.');
    }
}
