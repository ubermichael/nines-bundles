<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
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
    private bool $enabled = false;

    private ?string $url = null;

    /**
     * @var array<string,mixed>
     */
    private ?array $config = null;

    private ?LoggerPlugin $loggerPlugin = null;

    private ?ParameterBagInterface $parameters = null;

    /**
     * Singleton client instance.
     */
    private static ?Client $client = null;

    /**
     * Build and return a client configured with a Guzzle PSR7 adapter
     * and a logger plugin for debugging.
     *
     * @throws NotConfiguredException
     */
    public function build() : ?Client {
        if ( ! $this->enabled) {
            return null;
        }

        if (self::$client) {
            return self::$client;
        }

        if ( ! $this->url) {
            throw new NotConfiguredException('No solr URL configured.');
        }
        $parts = parse_url($this->url);
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

        $httpClient = new \GuzzleHttp\Client();

        $factory = new Psr17Factory();
        $adapter = new Psr18Adapter($httpClient, $factory, $factory);
        $eventDispatcher = new EventDispatcher();

        // create a client instance
        self::$client = new Client($adapter, $eventDispatcher, $this->config);
        self::$client->registerPlugin(LoggerPlugin::class, $this->loggerPlugin);

        return self::$client;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setLoggerPlugin(LoggerPlugin $loggerPlugin) : void {
        $this->loggerPlugin = $loggerPlugin;
    }

    public function reset() : void {
        self::$client = null;
        $this->config = [];
        $this->enabled = $this->parameters->get('nines_solr.enabled');
        $this->url = $this->parameters->get('nines_solr.url');
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setParameters(ParameterBagInterface $parameters) : void {
        $this->parameters = $parameters;
        $this->reset();
    }

    public function setEnabled(bool $enabled) : void {
        $this->enabled = $enabled;
    }

    public function setUrl(?string $url) : void {
        $this->url = $url;
    }

    /**
     * @return array<string,mixed>
     */
    public function getConfig() : array {
        return $this->config;
    }
}
