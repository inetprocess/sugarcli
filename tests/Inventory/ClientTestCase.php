<?php

namespace SugarCli\Tests\Inventory;

use Guzzle\Tests\GuzzleTestCase;
use Guzzle\Service\Client as GClient;
use Guzzle\Plugin\History\HistoryPlugin;

abstract class ClientTestCase extends GuzzleTestCase
{
    protected $history;

    abstract public function getClientType();

    public function getClient($mocks = array())
    {
        switch ($this->getClientType()) {
            case 'mock':
                $client = new GClient('test');
                $this->setMockBasePath(__DIR__ . '/rest_mock');
                if (!empty($mocks)) {
                    $this->setMockResponse($client, $mocks);
                }
                break;
            case 'real':
                $client = new GClient(
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
                break;
            default:
                throw new \Exception('Implement a method getClientType() to return \'mock\' or \'real\'.');
        }
        $this->history = new HistoryPlugin();
        $client->addSubscriber($this->history);
        return $client;
    }

    public function getHistory($client)
    {
        $history = null;
        foreach ($client->getEventDispatcher()->getListeners('request.sent') as $listener) {
            if ($listener[0] instanceof HistoryPlugin) {
                return $listener[0];
            }
        }
        return null;
    }
}
