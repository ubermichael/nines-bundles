<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Client;

use Solarium\Client;
use Solarium\Core\Client\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Builder {
    private $config;

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

    public function build(?AdapterInterface $adapter = null) {
        if ( ! $adapter) {
            $adapter = new \Solarium\Core\Client\Adapter\Curl();
        }
        $eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();

        return new Client($adapter, $eventDispatcher, $this->config);
    }
}
