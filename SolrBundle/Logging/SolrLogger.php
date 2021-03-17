<?php


namespace Nines\SolrBundle\Logging;


use Solarium\Core\Query\AbstractQuery;

class SolrLogger {

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var float|null
     */
    private $start;

    /**
     * @var AbstractQuery[]
     */
    private $queries;

    /**
     * @var int
     */
    private $current;

    public function __construct() {
        $this->queries = [];
        $this->current = 0;
    }

    public function startQuery($q, $params = []) {
        if( ! $this->enabled) {
            return;
        }
        $this->start = microtime(true);
        $this->queries[$this->current] = [
            'q' => $q,
            'params' => $params,
            'time' => 0,
        ];
    }

    public function stopQuery() {
        if( ! $this->enabled) {
            return;
        }
        $this->queries[$this->current]['time'] = microtime(true) - $this->start;
        $this->current++;
    }

    public function getQueries() {
        return $this->queries;
    }

}
