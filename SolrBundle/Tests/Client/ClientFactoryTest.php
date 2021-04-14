<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Tests\Client;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Nines\UtilBundle\Tests\BaseCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Psr18Adapter;

class ClientFactoryTest extends ClientBaseCase
{
    protected function pingResult() {
        return [
            'responseHeader' => [
                'QTime' => 150,
                'status' => 'OK',
            ]
        ];
    }

    public function testSetup() : void {
        $client = $this->getContainer()->get(Client::class);
        $this->assertNotNull($client);
    }

    public function testInjection() : void {
        $client = $this->mockClient($this->pingResult());

        $ping = $client->createPing();
        $result = $client->ping($ping);
        $json = json_decode($result->getResponse()->getBody());
        $this->assertSame(150, $json->responseHeader->QTime);
        $this->assertSame('OK', $json->responseHeader->status);
    }
}
