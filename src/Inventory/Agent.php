<?php

namespace SugarCli\Inventory;

use Guzzle\Http\Client;

use SugarCli\Sugar\Sugar;

/**
 * Send gathered facts to Inventory server.
 */
class Agent
{
    protected $server_url;
    protected $client_username;
    protected $client_password;

    protected $facts;
    protected $sugar;

    public function __construct($server_url, $client_username, $client_password, Sugar $sugar)
    {
        $this->server_url = $server_url;
        $this->client_username = $client_username;
        $this->client_password = $client_password;

        $this->facts = array(
            'system' => array(),
            'sugarcrm' => array(),
        );

        $this->sugar = $sugar;
    }

    public function populateFacts()
    {
        $sys_facter = new Facter();
        $this->facts['system'] = $sys_facter->getFacts();

        $sugar_facter = new SugarFacter($this->sugar);
        $this->facts['sugarcrm'] = $sugar_facter->getFacts();
    }

    public function sendInstance()
    {
    }

    public function getInstance()
    {
    }

    public function getServer()
    {
        $client = new Client($this->server_url);
        $client->setAuth($this->client_username, $this->client_password);

    }

    public function sendServer()
    {
    }
}
