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
    public function getClientType()
    {
        return 'real';
    }
    public function testSendServer()
    {
        $fqdn = 'agent.test';
        $client = $this->getClient();
        $agent = new Agent(new NullLogger(), $client);
        try {
            $client->deleteServer(array('fqdn' => $fqdn));
        } catch (\Exception $e) {
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
        $name = 'Test Corp.';
        $client = $this->getClient();
        $agent = new Agent(new NullLogger(), $client, $name);
        try {
            $client->deleteAccount(array('name' => $name));
        } catch (ClientErrorResponseException $e) {
            throw $e;
        }
        $history = $this->getHistory($client);
        // Should POST
        $agent->sendAccount();
        $this->assertEquals('POST', $history->getLastRequest()->getMethod());
        // Should PUT
        $agent->sendAccount();
        $this->assertEquals('PUT', $history->getLastRequest()->getMethod());
    }
}
