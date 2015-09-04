<?php

namespace SugarCli\Tests\Inventory;

use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Service\Client as GClient;
use Guzzle\Plugin\History\HistoryPlugin;

abstract class ClientTestCase extends GuzzleTestCase
{
    abstract public function getClientType();

    public function getClient($mocks = array())
    {
        switch ($this->getClientType()) {
            case 'mock':
                $client = $this->getMockClient($mocks);
                break;
            case 'real':
                $client = $this->getRealClient();
                break;
            default:
                throw new \Exception('Implement a method getClientType() to return \'mock\' or \'real\'.');
        }
        return $client;
    }

    public function getMockClient($mocks = array())
    {
        $client = new GClient('test');
        $this->setMockBasePath(__DIR__ . '/rest_mock');
        if (!empty($mocks)) {
            $this->setMockResponse($client, $mocks);
        }
        return $client;
    }

    public function getRealClient()
    {
        return new GClient(
            getenv('INVENTORY_URL'),
            array(
                'request.options' => array(
                    'auth' => array(
                        getenv('INVENTORY_USERNAME'),
                        getenv('INVENTORY_PASSWORD'),
                    ),
                ),
            )
        );
    }

    public function getHistory($client)
    {
        $history = new HistoryPlugin();
        $client->addSubscriber($history);
        return $history;
    }
}
