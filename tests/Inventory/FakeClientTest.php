<?php

namespace SugarCli\Tests\Inventory;

use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Service\Client as GClient;
use Guzzle\Service\Description\ServiceDescription;

use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

class FakeClientTest extends GuzzleTestCase
{
    public $fqdn = 'testserver.inetprocess.fr';

    public function getClient($mocks = array())
    {
        $client = new GClient('test');
        $client->setDescription(
            ServiceDescription::factory('src/Inventory/InventoryService.json')
        );
        $this->setMockBasePath(__DIR__ . '/rest_mock');
        if (!empty($mocks)) {
            $this->setMockResponse($client, $mocks);
        }
        return $client;
    }

    public function testPostServer()
    {
        $client = $this->getClient(array('post_server.http'));
        $cmd = $client->getCommand('postServer', array(
            'fqdn' => $this->fqdn,
            'facts' => array('test' => 'test', 'foo' => 'bar')
        ));
        $resp = $cmd->execute();
        $this->assertEquals(201, $cmd->getResponse()->getStatusCode());
        $this->assertStringEndsWith('/servers/' . $this->fqdn, $resp->get('location'));
    }

    public function testPutServer()
    {
        $client = $this->getClient(array('put_server.http'));
        $cmd = $client->getCommand('putServer', array(
            'fqdn_uri' => $this->fqdn,
            'fqdn' => $this->fqdn,
            'facts' => array('test' => 'test', 'bar' => 'foo')
        ));
        $cmd->execute();
        $this->assertEquals(204, $cmd->getResponse()->getStatusCode());
    }

    public function testGetOneServer()
    {
        $client = $this->getClient(array('get_one_server.http'));
        $cmd = $client->getCommand('getServer', (array('fqdn' => $this->fqdn)));
        $resp = $cmd->execute();
        $this->assertEquals($this->fqdn, $resp['fqdn']);
    }

    public function testGetServers()
    {
        $client = $this->getClient(array('get_servers.http'));
        $resp = $client->getServers();
        foreach ($resp as $server) {
            $this->assertArrayHasKey('id', $server);
            $this->assertArrayHasKey('fqdn', $server);
            $this->assertArrayHasKey('facts', $server);
            $this->assertArrayHasKey('sugar_instances', $server);
        }
    }

    public function testDeleteServer()
    {
        $client = $this->getClient(array('delete_server.http'));
        $cmd = $client->getCommand('deleteServer', array('fqdn' => $this->fqdn));
        $cmd->execute();
        $this->assertEquals(204, $cmd->getResponse()->getStatusCode());
    }
}
