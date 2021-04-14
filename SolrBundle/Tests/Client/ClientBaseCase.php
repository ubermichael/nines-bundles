<?php


namespace Nines\SolrBundle\Tests\Client;


use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Psr18Adapter;
use Nines\UtilBundle\Tests\BaseCase;

class ClientBaseCase extends BaseCase {

    protected function mockAdapter($data) {
        if(is_array($data)) {
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
