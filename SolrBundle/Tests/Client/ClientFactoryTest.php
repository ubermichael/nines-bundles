<?php


namespace Nines\SolrBundle\Tests\Client;


use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Nines\UtilBundle\Tests\BaseCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Psr18Adapter;

class ClientFactoryTest extends BaseCase {

    public function testSetup() {
        $client = $this->getContainer()->get(Client::class);
        $this->assertNotNull($client);
    }

    public function testInjection() {
        $client = $this->getContainer()->get(Client::class);

        $adapter = $this->mockAdapter($this->pingResult());
        $client->setAdapter($adapter);

        $ping = $client->createPing();
        $result = $client->ping($ping);
        $json = json_decode($result->getResponse()->getBody());
        $this->assertEquals(150, $json->responseHeader->QTime);
    }

    protected function mockAdapter($data) {
        $mock = new MockHandler([
            new Response(200, ['Content-Type: text/json'], $this->pingResult()),
        ]);
        $stack = HandlerStack::create($mock);

        $httpClient = new \GuzzleHttp\Client(['handler' => $stack]);
        $factory = new Psr17Factory();
        $adapter = new Psr18Adapter($httpClient, $factory, $factory);
        return $adapter;
    }

    protected function pingResult() {
        $json = <<<END_JSON
        {
            "responseHeader":{
                "status":0,
                "QTime":150,
                "status":"OK"
            }
        }
        END_JSON;
        return $json;
    }


}
