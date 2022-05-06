<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\DataCollector;

use Nines\SolrBundle\Logging\SolrLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

/**
 * Collect data for display in the Symfony debug toolbar.
 *
 * @see https://symfony.com/doc/current/profiler/data_collector.html
 */
class RequestCollector extends DataCollector {
    private ?SolrLogger $logger = null;

    public function __construct(SolrLogger $logger) {
        $this->logger = $logger;
    }

    /**
     * Called by the toolbar to gather the data from the loggers.
     *
     * @param ?Throwable $exception
     */
    public function collect(Request $request, Response $response, ?Throwable $exception = null) : void {
        $this->data = [
            'logs' => $this->logger->getLogs(),
            'counts' => $this->logger->getCounts(),
            'queries' => $this->logger->getQueries(),
        ];
    }

    /**
     * Get the log data for display.
     *
     * @return mixed
     */
    public function getLogs() {
        return $this->data['logs'];
    }

    /**
     * Get the queries executed in the server.
     *
     * @return mixed
     */
    public function getQueries() {
        return $this->data['queries'];
    }

    /**
     * Return an array counting the errors, warnings, etc.
     *
     * @return mixed
     */
    public function getCounts() {
        return $this->data['counts'];
    }

    /**
     * Name of the request collector.
     */
    public function getName() : string {
        return 'solr.request_collector';
    }

    /**
     * Clear the request collector.
     */
    public function reset() : void {
        $this->data = [];
    }
}
