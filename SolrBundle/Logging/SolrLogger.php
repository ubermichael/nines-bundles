<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Logging;

use Solarium\Core\Query\AbstractQuery;

class SolrLogger
{
    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var null|float
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

    public function startQuery($query, $params = []) : void {
        if ( ! $this->enabled) {
            return;
        }
        $this->start = microtime(true);
        $this->queries[$this->current] = [
            'query' => $query,
            'params' => $params,
            'time' => 0,
        ];
    }

    public function stopQuery() : void {
        if ( ! $this->enabled) {
            return;
        }
        $this->queries[$this->current]['time'] = microtime(true) - $this->start;
        $this->current++;
    }

    public function getQueries() {
        return $this->queries;
    }
}
