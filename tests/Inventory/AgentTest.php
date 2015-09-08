<?php

namespace SugarCli\Tests\Inventory;

use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Service\Client as GClient;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Http\Exception\ClientErrorResponseException;

use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

use Psr\Log\NullLogger;
use SugarCli\Inventory\Agent;
use SugarCli\Inventory\Facter\ArrayFacter;

class AgentTest extends ClientTestCase
{
    protected $account_name = 'Test Corp.';
    protected $server_fqdn = 'agent.test';
    protected $instance_id = 'test_agent_instance';

    public function getClientType()
    {
        return 'real';
    }
    public function testSendServer()
    {
        $fqdn = $this->server_fqdn;
        $client = $this->getClient();
        $agent = new Agent(new NullLogger(), $client);
        try {
            $client->deleteSugarInstance(array('instance_id' => $this->instance_id));
        } catch (ClientErrorResponseException $e) {
        }
        try {
            $client->deleteServer(array('fqdn' => $fqdn));
        } catch (\ClientErrorResponseException $e) {
        }
        $history = $this->getHistory($client);
        $agent->setFacter(new ArrayFacter(array(
            'fqdn' => $fqdn,
            'hostname' => $fqdn,
        )), Agent::SYSTEM);
        // Should POST
        $agent->sendServer();
        $this->assertEquals('POST', $history->getLastRequest()->getMethod());
        // Should PUT
        $agent->sendServer();
        $this->assertEquals('PUT', $history->getLastRequest()->getMethod());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage No facter found for this type. Please set the facter object first.
     */
    public function testSetWrongFacter()
    {
        $agent = new Agent(new NullLogger(), $this->getClient());
        $agent->getFacter(999999);
    }

    /**
     * @expectedException Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function testServerError()
    {
        $fqdn = 'error.400';
        $agent = new Agent(new NullLogger(), $this->getMockClient(array('error_400.http')));
        $agent->setFacter(new ArrayFacter(array(
            'fqdn' => $fqdn,
            'hostname' => $fqdn,
        )), Agent::SYSTEM);
        $agent->sendServer();
    }

    public function testSendAccount()
    {
        $name = $this->account_name;
        $client = $this->getClient();
        $agent = new Agent(new NullLogger(), $client, $name);
        try {
            $client->deleteSugarInstance(array('instance_id' => $this->instance_id));
        } catch (ClientErrorResponseException $e) {
        }
        try {
            $client->deleteAccount(array('name' => $name));
        } catch (ClientErrorResponseException $e) {
        }
        $history = $this->getHistory($client);
        // Should POST
        $agent->sendAccount();
        $this->assertEquals('POST', $history->getLastRequest()->getMethod());
        // Should PUT
        $agent->sendAccount();
        $this->assertEquals('PUT', $history->getLastRequest()->getMethod());
    }

    public function testGetServer()
    {
        $name = $this->server_fqdn;
        $client = $this->getClient();
        $agent = new Agent(new NullLogger(), $client, $name);
        $this->assertInternalType('integer', $agent->getServerId($name));
        $this->assertNull($agent->getServerId('invalid server'));
    }

    public function testGetAccount()
    {
        $name = $this->account_name;
        $client = $this->getClient();
        $agent = new Agent(new NullLogger(), $client, $name);
        $this->assertInternalType('integer', $agent->getAccountId($name));
        $this->assertNull($agent->getAccountId('invalid account'));
    }

    /**
     * @expectedException Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function testGetAccountError()
    {
        $fqdn = 'error.400';
        $agent = new Agent(new NullLogger(), $this->getMockClient(array('error_400.http')));
        $agent->getAccountId('invalid account');
    }


    public function testSendSugarInstance()
    {
        $name = $this->instance_id;
        $client = $this->getClient();
        $agent = new Agent(new NullLogger(), $client, $name);
        try {
            $client->deleteSugarInstance(array('instance_id' => $this->instance_id));
        } catch (ClientErrorResponseException $e) {
        }
        $history = $this->getHistory($client);
        $agent->setFacter(new ArrayFacter(array(
            'instance_id' => $name,
            'flavor' => 'PRO',
        )), Agent::SUGARCRM);
        // Should POST
        $agent->sendSugarInstance();
        $this->assertEquals('POST', $history->getLastRequest()->getMethod());
        // Should PUT
        $account_id = $agent->getAccountId($this->account_name);
        $this->assertInternalType('integer', $account_id);
        $server_id = $agent->getServerId($this->server_fqdn);
        $this->assertInternalType('integer', $server_id);
        $agent->sendSugarInstance($server_id, $account_id);
        $this->assertEquals('PUT', $history->getLastRequest()->getMethod());
    }
}
