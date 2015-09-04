<?php

namespace SugarCli\Inventory;

use Guzzle\Service\Client as GClient;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Http\Exception\ClientErrorResponseException;

use Inet\SugarCRM\Application;

/**
 * Send gathered facts to Inventory server.
 */
class Agent
{
    protected $facts;
    protected $sugarApp;
    protected $client;

    public function __construct(Application $sugarApp, GClient $client)
    {
        $this->facts = array(
            'system' => array(),
            'sugarcrm' => array(),
        );

        $this->sugarApp = $sugarApp;
        $this->client = $client;

        $this->client->setDescription(
            ServiceDescription::factory(__DIR__ . '/InventoryService.json')
        );
    }

    public function getApplication()
    {
        return $this->sugarApp;
    }

    public function getLogger()
    {
        return $this->getApplication()->getLogger();
    }

    public function getClient()
    {
        return $this->client;
    }

    public function populateFacts()
    {
        $sys_facter = new Facter();
        $this->facts['system'] = $sys_facter->getFacts();

        $sugar_facter = new SugarFacter($this->getApplication());
        $this->facts['sugarcrm'] = $sugar_facter->getFacts();
    }

    public function sendServer()
    {
        $fqdn = $this->facts['system']['hostname'];
        $client = $this->getClient();
        try {
            $server_data = $client->getServer(array('fqdn' => $fqdn))->toArray();
            $server_data['fqdn_uri'] = $fqdn;
            $server_data['facts'] = $this->facts['system'];
            $client->putServer($server_data);
        } catch (ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                // The server doesn't exist yet. We need to POST it.
                $server_data = array(
                    'fqdn' => $fqdn,
                    'facts' => $this->facts['system'],
                );
                $client->postServer($server_data);
            } else {
                // This is not a 404 error, throw the exception.
                throw $e;
            }
        }
    }
}
