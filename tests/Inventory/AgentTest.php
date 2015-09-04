<?php

namespace SugarCli\Tests\Inventory;

use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Service\Client as GClient;
use Guzzle\Service\Description\ServiceDescription;

use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

use Psr\Log\NullLogger;
use Inet\SugarCRM\Application;
use SugarCli\Inventory\Agent;

class AgentTest extends ClientTestCase
{
    public function getClientType()
    {
        return 'real';
    }
    public function testSendServer()
    {
        $client = $this->getClient();
        $agent = new Agent(new Application(new NullLogger, __DIR__ . '/fake_sugar'), $client);
        try {
            $client->deleteServer(array('fqdn' => gethostname()));
        } catch (\Exception $e) {
        }
        $history = $this->getHistory($client);
        $agent->populateFacts();
        // Should POST
        $agent->sendServer();
        $this->assertEquals('POST', $history->getLastRequest()->getMethod());
        // Should PUT
        $agent->sendServer();
        $this->assertEquals('PUT', $history->getLastRequest()->getMethod());
    }
}
