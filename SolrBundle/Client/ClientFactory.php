<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Client;

use Nyholm\Psr7\Factory\Psr17Factory;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Psr18Adapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ClientFactory
{
    private $config;

    /**
     * @var LoggerPlugin
     */
    private $loggerPlugin;

    /**
     * @var null Client
     */
    private static $client = null;

    public function __construct(ParameterBagInterface $parameters) {
        $this->config = [
            'endpoint' => [
                'host' => [
                    'host' => $parameters->get('nines.solr.host'),
                    'port' => $parameters->get('nines.solr.port'),
                    'path' => $parameters->get('nines.solr.path'),
                    'core' => $parameters->get('nines.solr.core'),
                ],
            ],
        ];
    }

    public function build() : Client {
        if ( ! self::$client) {
            $httpClient = new \GuzzleHttp\Client();

            $factory = new Psr17Factory();
            $adapter = new Psr18Adapter($httpClient, $factory, $factory);
            $eventDispatcher = new EventDispatcher();

            // create a client instance
            self::$client = new Client($adapter, $eventDispatcher, $this->config);
            self::$client->registerPlugin(LoggerPlugin::class, $this->loggerPlugin);
        }

        return self::$client;
    }

    /**
     * @required
     */
    public function setLoggerPlugin(LoggerPlugin $loggerPlugin) : void {
        $this->loggerPlugin = $loggerPlugin;
    }
}
