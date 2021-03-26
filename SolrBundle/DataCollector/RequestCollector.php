<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\DataCollector;

use Nines\SolrBundle\Logging\SolrLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

class RequestCollector extends DataCollector
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var SolrLogger
     */
    private $logger;

    public function __construct(SolrLogger $logger) {
        $this->logger = $logger;
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null) : void {
        $this->data = [
            'logs' => $this->logger->getLogs(),
            'counts' => $this->logger->getCounts(),
            'queries' => $this->logger->getQueries(),
        ];
    }

    public function getLogs() {
        return $this->data['logs'];
    }

    public function getQueries() {
        return $this->data['queries'];
    }

    public function getCounts() {
        return $this->data['counts'];
    }

    public function getName() {
        return 'solr.request_collector';
    }

    public function reset() : void {
        $this->data = [];
    }
}
