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

class ClientBaseCase extends BaseCase {
    protected function mockAdapter($data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $mock = new MockHandler([
            new Response(200, ['Content-Type: text/json'], $data),
        ]);
        $stack = HandlerStack::create($mock);

        $httpClient = new \GuzzleHttp\Client(['handler' => $stack]);
        $factory = new Psr17Factory();

        return new Psr18Adapter($httpClient, $factory, $factory);
    }

    protected function mockClient($data) {
        $client = $this->getContainer()->get(Client::class);
        $adapter = $this->mockAdapter($data);
        $client->setAdapter($adapter);

        return $client;
    }
}
