<?php

namespace SugarCli\Tests\Inventory;

use Guzzle\Service\Client as GClient;
use Guzzle\Service\Description\ServiceDescription;

/**
 * @group inventory
 */
class RealClientTest extends FakeClientTest
{
    public $fqdn = 'testserver.inetprocess.fr';

    public function getClient($mock = array())
    {
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
        $client->setDescription(
            ServiceDescription::factory('src/Inventory/InventoryService.json')
        );
        return $client;
    }
}
