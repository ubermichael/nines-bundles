<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Client;

use Nines\SolrBundle\Exception\NotConfiguredException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Psr18Adapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Build and return a client for use in Symfony's dependency injection.
 */
class ClientFactory {
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var array
     */
    private $config;

    /**
     * @var LoggerPlugin
     */
    private $loggerPlugin;

    /**
     * Singleton client instance.
     *
     * @var null|Client
     */
    private static $client = null;

    public function __construct(ParameterBagInterface $parameters) {
        $this->enabled = $parameters->get('nines_solr.enabled');
        if ( ! $this->enabled) {
            return;
        }

        $url = $parameters->get('nines.solr.url');
        if( ! $url) {
            throw new NotConfiguredException("No solr URL configured.");
        }
        $parts = parse_url($url);
        $matches = [];
        preg_match('|^(.*?/)(\\w+)$|', $parts['path'], $matches);

        $this->config = [
            'endpoint' => [
                'host' => [
                    'host' => $parts['host'],
                    'port' => $parts['port'],
                    'path' => $matches[1],
                    'core' => $matches[2],
                ],
            ],
        ];
    }

    /**
     * Build and return a client configured with a Guzzle PSR7 adapter
     * and a logger plugin for debugging.
     */
    public function build() : ?Client {
        if ( ! $this->enabled) {
            return null;
        }
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
