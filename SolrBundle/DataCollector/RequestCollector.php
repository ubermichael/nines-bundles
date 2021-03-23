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

    public function collect(Request $request, Response $response) : void {
        $this->data = [
            'queries' => $this->logger->getQueries(),
        ];
    }

    public function getQueries() {
        return $this->data['queries'];
    }

    public function getName() {
        return 'solr.request_collector';
    }

    public function reset() : void {
        $this->data = [];
    }
}
