<?php


namespace Nines\SolrBundle\DataCollector;


use Nines\SolrBundle\Logging\SolrLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class RequestCollector extends DataCollector {

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

    public function collect(Request $request, Response $response) {
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

    public function reset() {
        $this->data = [];
    }
}
